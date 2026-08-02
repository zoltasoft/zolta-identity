<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\DeleteRole;

use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(DeleteRoleCommand::class)]
final readonly class DeleteRoleCommandHandler
{
    public function __construct(
        private RoleRepository $roleRepository,
    ) {}

    public function __invoke(DeleteRoleCommand $deleteRoleCommand): Result
    {
        $role = $this->roleRepository->findRoleById($deleteRoleCommand->roleId);
        if ($role === null) {
            return Result::failure(new RuntimeException('Role not found'));
        }

        if ($role->isSystemRole()) {
            return Result::failure(new RuntimeException('System roles cannot be deleted'));
        }

        $this->roleRepository->deleteRole($role);

        return Result::success();
    }
}
