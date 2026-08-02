<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromRole;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\RoleId;

#[HandlesCommand(RevokePermissionFromRoleCommand::class)]
final readonly class RevokePermissionFromRoleCommandHandler
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(RevokePermissionFromRoleCommand $revokePermissionFromRoleCommand): Result
    {
        $permissionId = $revokePermissionFromRoleCommand->permissionId instanceof PermissionId
            ? $revokePermissionFromRoleCommand->permissionId
            : new PermissionId((string) $revokePermissionFromRoleCommand->permissionId);

        $roleId = $revokePermissionFromRoleCommand->roleId instanceof RoleId
            ? $revokePermissionFromRoleCommand->roleId
            : new RoleId((string) $revokePermissionFromRoleCommand->roleId);

        $permission = $this->permissionRepository->findPermissionById($permissionId);
        if ($permission === null) {
            return Result::failure(new RuntimeException('Permission not found'));
        }

        $permission->revokeFromRole($roleId);

        $this->permissionRepository->updatePermission($permission);

        return Result::success(new PermissionPayload($permission));
    }
}
