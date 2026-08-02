<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\AccountSecurityServiceInterface;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Exceptions\ValidationException;

final class LaravelAccountSecurityService implements AccountSecurityServiceInterface
{
    public function listSessions(UserId $userId, ?int $currentTokenId = null): array
    {
        return PersonalAccessToken::query()
            ->where('tokenable_id', $userId->get('value'))
            ->latest('last_used_at')
            ->latest('created_at')
            ->get()
            ->map(static fn (PersonalAccessToken $token): array => [
                'id' => (string) $token->getKey(),
                'name' => $token->name,
                'current' => $currentTokenId !== null && (int) $token->getKey() === $currentTokenId,
                'last_active_at' => $token->last_used_at?->toAtomString(),
                'created_at' => $token->created_at?->toAtomString(),
                'expires_at' => $token->expires_at?->toAtomString(),
            ])
            ->all();
    }

    public function revokeSession(UserId $userId, int $tokenId): void
    {
        $deleted = PersonalAccessToken::query()
            ->whereKey($tokenId)
            ->where('tokenable_id', $userId->get('value'))
            ->delete();

        if ($deleted === 0) {
            throw new ValidationException([
                'session' => ['The selected session no longer exists.'],
            ]);
        }
    }

    public function changePassword(
        UserId $userId,
        string $currentPassword,
        string $newPassword,
        ?int $currentTokenId = null,
    ): void {
        $user = User::query()->findOrFail($userId->get('value'));

        if (! Hash::check($currentPassword, $user->password)) {
            throw new ValidationException([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->forceFill(['password' => $newPassword])->save();

        $tokens = $user->tokens();
        if ($currentTokenId !== null) {
            $tokens->where('id', '!=', $currentTokenId);
        }
        $tokens->delete();
    }
}
