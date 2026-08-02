<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\UpdateRole;

use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(UpdateRoleCommand::class)]
final readonly class UpdateRoleCommandValidator
{
    public function __construct(private RoleRepository $roleRepository) {}

    public function __invoke(UpdateRoleCommand $updateRoleCommand): Result
    {
        if ($updateRoleCommand->name === null) {
            return Result::success();
        }

        $existing = $this->roleRepository->findRoleByName($updateRoleCommand->name);
        if ($existing !== null && $existing->getId()->get('value') !== $updateRoleCommand->roleId->get('value')) {
            return Result::failure(new RuntimeException('Role name already exists'));
        }

        return Result::success();
    }
}
