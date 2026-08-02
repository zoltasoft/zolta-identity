<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\CreateRole;

use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\Exceptions\ValidationException;

#[ValidatesCommand(CreateRoleCommand::class)]
final readonly class CreateRoleCommandValidator
{
    public function __construct(private RoleRepository $roleRepository) {}

    public function __invoke(CreateRoleCommand $createRoleCommand): Result
    {
        $existing = $this->roleRepository->findRoleByName($createRoleCommand->name);
        if ($existing !== null) {
            return Result::failure(new ValidationException([
                'Role name' => 'The role name already exists',
            ]));
        }

        return Result::success();
    }
}
