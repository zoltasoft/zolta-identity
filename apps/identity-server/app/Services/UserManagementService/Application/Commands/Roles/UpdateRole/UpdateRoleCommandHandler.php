<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\UpdateRole;

use App\Services\UserManagementService\Application\Payloads\Roles\RolePayload;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(UpdateRoleCommand::class)]
final readonly class UpdateRoleCommandHandler
{
    public function __construct(
        private RoleRepository $roleRepository,
    ) {}

    public function __invoke(UpdateRoleCommand $updateRoleCommand): Result
    {
        $role = $this->roleRepository->findRoleById($updateRoleCommand->roleId);
        if ($role === null) {
            return Result::failure(new RuntimeException('Role not found'));
        }

        if ($updateRoleCommand->name !== null) {
            $role->rename($updateRoleCommand->name);
        }

        if ($updateRoleCommand->description !== null) {
            $role->changeDescription($updateRoleCommand->description);
        }

        $this->roleRepository->updateRole($role);

        return Result::success(new RolePayload($role));
    }
}
