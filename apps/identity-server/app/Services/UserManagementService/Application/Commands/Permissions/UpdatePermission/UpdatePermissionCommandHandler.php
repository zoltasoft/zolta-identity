<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\UpdatePermission;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

#[HandlesCommand(UpdatePermissionCommand::class)]
final readonly class UpdatePermissionCommandHandler
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(UpdatePermissionCommand $updatePermissionCommand): Result
    {
        $permission = $this->permissionRepository->findPermissionById($updatePermissionCommand->permissionId);
        if ($permission === null) {
            return Result::failure(new RuntimeException('Permission not found'));
        }

        if ($updatePermissionCommand->name !== null) {
            $permission->rename($updatePermissionCommand->name);
        }

        if ($updatePermissionCommand->description !== null) {
            $permission->changeDescription($updatePermissionCommand->description);
        }

        if ($updatePermissionCommand->roleIds !== null) {
            $roleIds = array_map(
                static fn ($roleId): RoleId => $roleId instanceof RoleId ? $roleId : new RoleId((string) $roleId),
                $updatePermissionCommand->roleIds
            );

            $permission->syncRoles($roleIds);
        }

        if ($updatePermissionCommand->userIds !== null) {
            $userIds = array_map(
                static fn ($userId): UserId => $userId instanceof UserId ? $userId : new UserId((string) $userId),
                $updatePermissionCommand->userIds
            );

            $permission->syncUsers($userIds);
        }

        $this->permissionRepository->updatePermission($permission);

        return Result::success(new PermissionPayload($permission));
    }
}
