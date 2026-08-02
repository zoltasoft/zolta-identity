<?php

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Entities\OAuthAccount;
use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use App\Services\UserManagementService\Domain\Repositories\OAuthAccountRepository;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\SocialAccount as EloquentOAuthAccount;
use DateTimeImmutable;
use Zolta\Cqrs\Laravel\Eloquent\EloquentBaseRepository;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\AvatarUrl;
use Zolta\Domain\ValueObjects\OAuthAccountId;
use Zolta\Domain\ValueObjects\OAuthProviderId;
use Zolta\Domain\ValueObjects\RefreshToken;
use Zolta\Domain\ValueObjects\UserId;

final class EloquentOAuthAccountRepository extends EloquentBaseRepository implements OAuthAccountRepository
{
    protected array $allowedRelations = [];

    protected function modelClass(): string
    {
        return EloquentOAuthAccount::class;
    }

    protected function getAllowedRelations(): array
    {
        return [];
    }

    public function saveOAuthAccount(OAuthAccount $account): void
    {
        EloquentOAuthAccount::updateOrCreate(
            [
                'user_id' => $account->getUserId()->get('value'),
                'social_provider_id' => $account->getOAuthProviderId()->get('value'),
            ],
            [
                'id' => $account->getId()->get('value'),
                'social_provider_user_id' => $account->getOAuthProviderUserId(),
                'access_token' => $account->getAccessToken()->get('token'),
                'access_token_expires_at' => $account->getAccessToken()->get('expiresAt')->format('Y-m-d H:i:s'),
                'refresh_token' => $account->getRefreshToken()->get('token'),
                'refresh_token_expires_at' => $account->getRefreshToken()->get('expiresAt')?->format('Y-m-d H:i:s'),
                'avatar_url' => $account->getAvatarUrl()->get('url'),
            ]
        );
    }

    public function findOAuthAccountByToken(OAuthProvider $provider, string $token): ?OAuthAccount
    {
        $model = EloquentOAuthAccount::query()
            ->with('provider')
            ->where('social_provider_id', $provider->getId()->get('value'))
            ->where('access_token', $token)
            ->first();

        return $model ? $this->mapToDomain($model) : null;
    }

    public function deleteByUserId(UserId $userId): void
    {
        EloquentOAuthAccount::query()
            ->where('user_id', $userId->get('value'))
            ->delete();
    }

    public function findByProviderAndProviderUserId(OAuthProviderId $providerId, string $providerUserId): ?OAuthAccount
    {
        $model = EloquentOAuthAccount::query()
            ->with('provider')
            ->where('social_provider_id', $providerId->get('value'))
            ->where('social_provider_user_id', $providerUserId)
            ->first();

        return $model ? $this->mapToDomain($model) : null;
    }

    private function mapToDomain(EloquentOAuthAccount $model): OAuthAccount
    {
        $accessToken = new AccessToken(
            $model->access_token,
            new DateTimeImmutable((string) $model->access_token_expires_at)
        );

        $refreshToken = null;
        if (! empty($model->refresh_token) && ! empty($model->refresh_token_expires_at)) {
            $refreshToken = new RefreshToken(
                $model->refresh_token,
                new DateTimeImmutable((string) $model->refresh_token_expires_at)
            );
        }

        $avatarValue = ! empty($model->avatar_url)
            ? (string) $model->avatar_url
            : sprintf('https://www.gravatar.com/avatar/%s', md5((string) $model->user_id));

        $avatarUrl = new AvatarUrl($avatarValue);

        $providerName = null;
        if ($model->relationLoaded('provider') && $model->provider !== null) {
            $providerName = $model->provider->social_provider;
        }

        return OAuthAccount::restore(
            new OAuthAccountId((string) $model->id),
            new UserId((string) $model->user_id),
            new OAuthProviderId((string) $model->social_provider_id),
            (string) $model->social_provider_user_id,
            $accessToken,
            $refreshToken ?? RefreshToken::generate(),
            $avatarUrl,
            $providerName,
            new DateTimeImmutable((string) $model->created_at),
            new DateTimeImmutable((string) $model->updated_at)
        );
    }
}
