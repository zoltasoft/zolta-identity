<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\DeletePermission;

use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(DeletePermissionCommand::class)]
final readonly class DeletePermissionCommandHandler
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(DeletePermissionCommand $deletePermissionCommand): Result
    {
        $permission = $this->permissionRepository->findPermissionById($deletePermissionCommand->permissionId);
        if ($permission === null) {
            return Result::failure(new RuntimeException('Permission not found'));
        }

        $this->permissionRepository->deletePermission($permission);

        return Result::success();
    }
}
