<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Application\Exceptions\IdentityAuthenticationException;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityRefreshToken;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

final class IdentityTokenManager
{
    /** @return array<string, mixed> */
    public function issuePair(
        User $user,
        IdentityProject $project,
        IdentityProjectClient $client,
        IdentityProjectMembership $membership,
        ?string $familyId = null,
    ): array {
        $familyId ??= (string) Str::uuid();
        $accessExpiresAt = now()->addMinutes((int) config('zolta.identity.access_token_ttl_minutes', 15));
        $refreshExpiresAt = now()->addDays((int) config('zolta.identity.refresh_token_ttl_days', 30));

        if ($user->is_temporary && $user->demo_expires_at !== null) {
            if ($user->demo_expires_at->isPast()) {
                throw new IdentityAuthenticationException('Temporary identity has expired.');
            }

            $accessExpiresAt = $user->demo_expires_at->lessThan($accessExpiresAt)
                ? $user->demo_expires_at->copy()
                : $accessExpiresAt;
            $refreshExpiresAt = $user->demo_expires_at->lessThan($refreshExpiresAt)
                ? $user->demo_expires_at->copy()
                : $refreshExpiresAt;
        }

        $access = $user->createToken(
            'identity:'.$project->slug,
            $membership->effectivePermissionKeys(),
            $accessExpiresAt,
        );
        $access->accessToken->forceFill([
            'identity_project_id' => $project->id,
            'identity_client_id' => $client->id,
            'identity_refresh_family_id' => $familyId,
        ])->save();

        $refreshPlain = Str::random(96);
        IdentityRefreshToken::query()->create([
            'family_id' => $familyId,
            'user_id' => $user->id,
            'project_id' => $project->id,
            'client_id' => $client->id,
            'token_hash' => hash('sha256', $refreshPlain),
            'expires_at' => $refreshExpiresAt,
        ]);

        return [
            'token_type' => 'Bearer',
            'access_token' => $access->plainTextToken,
            'access_token_expires_at' => $accessExpiresAt->toIso8601String(),
            'expires_in' => $accessExpiresAt->diffInSeconds(now()),
            'refresh_token' => $refreshPlain,
            'refresh_token_expires_at' => $refreshExpiresAt->toIso8601String(),
        ];
    }

    public function revokeFamily(string $familyId): void
    {
        IdentityRefreshToken::query()
            ->where('family_id', $familyId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
        PersonalAccessToken::query()->where('identity_refresh_family_id', $familyId)->delete();
    }

    public function revokeUser(string $userId): void
    {
        IdentityRefreshToken::query()
            ->where('user_id', $userId)
            ->pluck('family_id')
            ->unique()
            ->each(fn (string $familyId) => $this->revokeFamily($familyId));
        PersonalAccessToken::query()->where('tokenable_id', $userId)->delete();
    }

    public function revokeClient(string $clientId): void
    {
        IdentityRefreshToken::query()
            ->where('client_id', $clientId)
            ->pluck('family_id')
            ->unique()
            ->each(fn (string $familyId) => $this->revokeFamily($familyId));
        PersonalAccessToken::query()->where('identity_client_id', $clientId)->delete();
    }

    public function revokeProjectUser(string $projectId, string $userId): void
    {
        IdentityRefreshToken::query()
            ->where('user_id', $userId)
            ->where('project_id', $projectId)
            ->update(['revoked_at' => now()]);
        PersonalAccessToken::query()
            ->where('tokenable_id', $userId)
            ->where('identity_project_id', $projectId)
            ->delete();
    }

    public function revokeProject(string $projectId): void
    {
        IdentityRefreshToken::query()
            ->where('project_id', $projectId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
        PersonalAccessToken::query()
            ->where('identity_project_id', $projectId)
            ->delete();
    }
}
