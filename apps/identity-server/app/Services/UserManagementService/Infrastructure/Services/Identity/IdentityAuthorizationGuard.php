<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Application\Exceptions\IdentityAuthorizationException;
use App\Services\UserManagementService\Application\Exceptions\IdentityProjectLifecycleException;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectMembershipRepository;

final readonly class IdentityAuthorizationGuard
{
    public function __construct(
        private EloquentIdentityProjectMembershipRepository $memberships,
    ) {}

    public function assertProjectAdministrator(string $userId, string $projectId, bool $requireActiveProject = true): void
    {
        $user = User::query()->findOrFail($userId);
        if (! $user->is_system_admin) {
            $membership = $this->memberships->findActiveForProjectUser($projectId, $userId);
            if (! $membership
                || (! $membership->is_admin
                    && ! in_array('identity.project.manage', $membership->effectivePermissionKeys(), true))) {
                throw new IdentityAuthorizationException('Project administrator access is required.');
            }
        }

        if ($requireActiveProject) {
            $project = IdentityProject::query()->select(['id', 'status'])->find($projectId);
            if ($project === null) {
                throw new IdentityAuthorizationException('This project is not available for changes.');
            }
            if ($project->status === 'pending_deletion') {
                throw new IdentityProjectLifecycleException(
                    'Project deletion is scheduled. Changes are unavailable until deletion is cancelled.',
                );
            }
            if ($project->status === 'suspended') {
                throw new IdentityProjectLifecycleException(
                    'This project is suspended. Changes are unavailable until it is reactivated.',
                );
            }
            if ($project->status !== 'active') {
                throw new IdentityProjectLifecycleException('This project is not available for changes.');
            }
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
