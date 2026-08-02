<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\IdentityInstallationServiceInterface;
use App\Services\UserManagementService\Application\Exceptions\IdentityAuthorizationException;
use App\Services\UserManagementService\Domain\Policies\IdentityAdministrationPolicy;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityAuditRecorder;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityAuthorizationGuard;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityTokenManager;

final readonly class EloquentIdentityInstallationService implements IdentityInstallationServiceInterface
{
    public function __construct(
        private IdentityAuthorizationGuard $authorization,
        private IdentityAdministrationPolicy $administrationPolicy,
        private IdentityTokenManager $tokens,
        private IdentityAuditRecorder $audit,
    ) {}

    public function listInstallationUsers(string $actorUserId): array
    {
        $this->authorization->assertInstallationAdministrator($actorUserId);

        return User::query()
            ->withCount('identityMemberships')
            ->orderBy('email')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'is_system_admin' => $user->is_system_admin,
                'locked' => $user->locked,
                'project_count' => $user->identity_memberships_count,
                'created_at' => $user->created_at?->toIso8601String(),
            ])->all();
    }

    public function updateInstallationUser(
        string $actorUserId,
        string $userId,
        bool $isSystemAdmin,
        bool $locked,
    ): void {
        $this->authorization->assertInstallationAdministrator($actorUserId);

        if (! $this->administrationPolicy->canUpdateInstallationAccount(
            $actorUserId,
            $userId,
            $isSystemAdmin,
            $locked,
        )) {
            throw new IdentityAuthorizationException(
                'Installation administrators cannot lock or demote their own account.',
            );
        }

        $user = User::query()->findOrFail($userId);
        $user->forceFill([
            'is_system_admin' => $isSystemAdmin,
            'locked' => $locked,
        ])->save();

        if ($locked) {
            $this->tokens->revokeUser($userId);
        }

        $this->audit->record(
            'installation_user.updated',
            null,
            null,
            $actorUserId,
            'user',
            $userId,
            [
                'is_system_admin' => $isSystemAdmin,
                'locked' => $locked,
            ],
        );
    }
}
