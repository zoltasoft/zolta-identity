<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\CreateRole;

use App\Services\UserManagementService\Application\Payloads\Roles\RolePayload;
use App\Services\UserManagementService\Domain\Factories\RoleFactory;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(CreateRoleCommand::class)]
final readonly class CreateRoleCommandHandler
{
    public function __construct(
        private RoleFactory $roleFactory,
        private RoleRepository $roleRepository,
    ) {}

    public function __invoke(CreateRoleCommand $createRoleCommand): Result
    {
        $role = $this->roleFactory->create($createRoleCommand->name, $createRoleCommand->description);

        $this->roleRepository->saveRole($role);

        return Result::success(new RolePayload($role));
    }
}
