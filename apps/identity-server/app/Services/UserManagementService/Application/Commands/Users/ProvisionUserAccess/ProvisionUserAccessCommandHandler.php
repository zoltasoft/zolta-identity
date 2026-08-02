<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\ProvisionUserAccess;

use App\Services\UserManagementService\Application\Payloads\Users\ProvisionedUserAccessPayload;
use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

#[HandlesCommand(ProvisionUserAccessCommand::class)]
final readonly class ProvisionUserAccessCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private PermissionRepository $permissionRepository,
    ) {}

    public function __invoke(ProvisionUserAccessCommand $provisionUserAccessCommand): Result
    {
        $userId = new UserId($provisionUserAccessCommand->userId);
        $roleId = new RoleId($provisionUserAccessCommand->roleId);

        $user = $this->userRepository->findUserById($userId);
        if ($user === null) {
            return Result::failure(new RuntimeException('User not found'));
        }

        $role = $this->roleRepository->findRoleById($roleId);
        if ($role === null) {
            return Result::failure(new RuntimeException('Role not found'));
        }

        /** @var Permission[] $permissions */
        $permissions = [];
        foreach ($provisionUserAccessCommand->permissionIds as $permissionId) {
            $permission = $this->permissionRepository->findPermissionById(new PermissionId($permissionId));
            if ($permission === null) {
                return Result::failure(new RuntimeException("Permission {$permissionId} not found"));
            }

            $permissions[] = $permission;
        }

        // Assign the role to the user
        $user->assignRole($roleId);

        // Make sure the role references the user for future queries
        $role->assignToUser($userId);

        // Attach permissions to both the role (optionally) and the user
        $updatedPermissions = [];
        foreach ($permissions as $permission) {
            if ($provisionUserAccessCommand->attachPermissionsToRole) {
                $role->assignPermission($permission);
                $permission->assignToRole($roleId);
            }

            $permission->assignToUser($userId);
            $updatedPermissions[] = $permission;
        }

        $this->userRepository->updateUser($user);
        $this->roleRepository->updateRole($role);

        foreach ($updatedPermissions as $updatedPermission) {
            $this->permissionRepository->updatePermission($updatedPermission);
        }

        return Result::success(new ProvisionedUserAccessPayload($user, $role, $updatedPermissions));
    }
}
