<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToRole;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(AssignPermissionToRoleCommand::class)]
final readonly class AssignPermissionToRoleCommandHandler
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(AssignPermissionToRoleCommand $assignPermissionToRoleCommand): Result
    {

        $permission = $this->permissionRepository->findPermissionById($assignPermissionToRoleCommand->permissionId);
        if ($permission === null) {
            return Result::failure(new RuntimeException('Permission not found'));
        }

        $permission->assignToRole($assignPermissionToRoleCommand->roleId);

        $this->permissionRepository->updatePermission($permission);

        return Result::success(new PermissionPayload($permission));
    }
}
