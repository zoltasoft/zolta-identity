<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Application\Exceptions\IdentityAuthorizationException;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectMembershipRepository;

final readonly class IdentityAuthorizationGuard
{
    public function __construct(
        private EloquentIdentityProjectMembershipRepository $memberships,
    ) {}

    public function assertProjectAdministrator(string $userId, string $projectId): void
    {
        $user = User::query()->findOrFail($userId);
        if ($user->is_system_admin) {
            return;
        }

        $membership = $this->memberships->findActiveForProjectUser($projectId, $userId);
        if (! $membership
            || (! $membership->is_admin
                && ! in_array('identity.project.manage', $membership->effectivePermissionKeys(), true))) {
            throw new IdentityAuthorizationException('Project administrator access is required.');
        }
    }

    public function assertInstallationAdministrator(string $userId): void
    {
        $user = User::query()->findOrFail($userId);
        if (! $user->is_system_admin) {
            throw new IdentityAuthorizationException('Installation administrator access is required.');
        }
    }
}
