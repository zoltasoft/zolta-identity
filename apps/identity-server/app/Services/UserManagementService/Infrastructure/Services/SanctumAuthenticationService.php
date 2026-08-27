<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\AuthenticationServiceInterface;
use App\Services\UserManagementService\Application\Contracts\RateLimitingServiceInterface;
use App\Services\UserManagementService\Domain\Aggregates\User as DomainUser;
use App\Services\UserManagementService\Infrastructure\Mappers\UserMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User as EloquentUser;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Zolta\Cqrs\Laravel\Eloquent\EloquentBaseRepository;
use Zolta\Cqrs\Repositories\Query\QueryOptions;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\UserCredential;
use Zolta\Domain\ValueObjects\UserId;

/**
 * Sanctum-based Authentication Service using Eloquent repository features.
 */
class SanctumAuthenticationService extends EloquentBaseRepository implements AuthenticationServiceInterface
{
    private const TOKEN_NAME = 'access_token';

    protected bool $enableReadCaching = false;

    private const TOKEN_EXPIRATION_HOURS = 2;

    private const RATE_LIMIT_ATTEMPTS = 50;

    private const RATE_LIMIT_DECAY_MINUTES = 1;

    public function __construct(
        private readonly RateLimitingServiceInterface $rateLimiter,
    ) {}

    /** @var list<string> */
    protected array $allowedFilters = [
        'email',
        'username',
        'status',
        'created_at',
        'updated_at',
        'last_login_at',
        'email_verified_at',
    ];

    /** @var array<string, list<string>> */
    protected array $filterableRelations = [];

    protected function modelClass(): string
    {
        return EloquentUser::class;
    }

    protected function getAllowedRelations(): array
    {
        return ['socialAccounts'];
    }

    // -------------------- Authentication Methods --------------------

    public function generateTokenFromUser(UserId $userId): AccessToken
    {
        $eloquentUser = $this->resolveEloquentUser($userId->get('value'));

        return $this->createToken($eloquentUser);
    }

    public function revokeUserToken(string $tokenId): void
    {
        // Extract the token hash part if needed
        if (Str::contains($tokenId, '|')) {
            [, $token] = explode('|', $tokenId, 2);
        } else {
            $token = $tokenId;
        }

        // Match against hashed token column
        $hashed = hash('sha256', $token);

        PersonalAccessToken::where('token', $hashed)->delete();
    }

    public function revokeAllUserTokens(UserId $userId): void
    {
        PersonalAccessToken::where('tokenable_id', $userId->get('value'))->delete();
    }

    public function generateAuthUserToken(): AccessToken
    {
        /** @var EloquentUser $user */
        $user = Auth::user();

        return $this->createToken($user);
    }

    public function attemptLogin(UserCredential $userCredential): bool
    {
        $rateLimitKey = $this->rateLimitKey($userCredential);
        $this->ensureIsNotRateLimited($rateLimitKey);

        try {
            $result = Auth::attempt([
                'email' => $userCredential->get('email')->get('address'),
                'password' => $userCredential->get('password')->get('hash'),
                static function (Builder $query): void {
                    $query
                        ->where(static function (Builder $temporary): void {
                            $temporary->where('is_temporary', false)
                                ->orWhere('demo_expires_at', '>', now());
                        })
                        ->where(static function (Builder $lock): void {
                            $lock->where('locked', false)
                                ->orWhere('lock_expiry', '<=', now());
                        });
                },
            ]);
        } catch (\Throwable $exception) {
            $this->rateLimiter->hit($rateLimitKey, self::RATE_LIMIT_DECAY_MINUTES);
            throw $exception;
        }

        if (! $result) {
            $this->rateLimiter->hit($rateLimitKey, self::RATE_LIMIT_DECAY_MINUTES);

            return false;
        }

        $this->rateLimiter->clear($rateLimitKey);

        return true;
    }

    public function logout(): void
    {
        /** @var EloquentUser|null $user */
        $user = Auth::user();
        if (! $user) {
            return;
        }

        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }
    }

    /**
     * Get the currently authenticated user as a DomainUser,
     * with optional includes via QueryOptions.
     */
    public function getAuthenticatedUser(?QueryOptions $opts = null): ?DomainUser
    {
        /** @var EloquentUser|null $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            return null;
        }

        $query = $this->repositoryQuery($opts);
        $include = $query->includes();

        // Reload with includes if necessary
        if (! empty($include)) {
            /** @var EloquentUser|null $authUser */
            $authUser = $this->modelClass()::with($include)
                ->find($authUser->getKey());
        }

        return $authUser ? UserMapper::toDomain($authUser) : null;
    }

    // -------------------- Helpers --------------------

    private function resolveEloquentUser(string $userId): EloquentUser
    {
        return EloquentUser::findOrFail($userId);
    }

    private function createToken(EloquentUser $user): AccessToken
    {
        $expiresAt = now()->addHours(self::TOKEN_EXPIRATION_HOURS);
        if ($user->is_temporary) {
            if ($user->demo_expires_at === null || $user->demo_expires_at->isPast()) {
                throw new \RuntimeException('This temporary demo account has expired.');
            }

            $expiresAt = $user->demo_expires_at->lessThan($expiresAt)
                ? $user->demo_expires_at->copy()
                : $expiresAt;
        }

        $token = $user->createToken(self::TOKEN_NAME, ['*'], $expiresAt);
        /** @var PersonalAccessToken&object{expires_at: Carbon|null} $tokenModel */
        $tokenModel = $token->accessToken;

        return AccessToken::resolve([
            'token' => $token->plainTextToken,
            'expiresAt' => new DateTimeImmutable($tokenModel->expires_at->toDateTimeString()),

        ]);
    }

    private function ensureIsNotRateLimited(string $key): void
    {
        if (! $this->rateLimiter->tooManyAttempts($key, self::RATE_LIMIT_ATTEMPTS, self::RATE_LIMIT_DECAY_MINUTES)) {
            return;
        }

        $seconds = $this->rateLimiter->availableIn($key, self::RATE_LIMIT_ATTEMPTS, self::RATE_LIMIT_DECAY_MINUTES) ?? 60;
        throw new \RuntimeException("Too many login attempts. Please try again in {$seconds} seconds.");
    }

    private function rateLimitKey(UserCredential $credential): string
    {
        $email = $credential->get('email')->get('address');

        return 'login_attempts_'.md5(strtolower(trim($email)));
    }
}
