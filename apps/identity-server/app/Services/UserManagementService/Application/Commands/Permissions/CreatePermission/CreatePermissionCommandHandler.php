<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\CreatePermission;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Domain\Factories\PermissionFactory;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(CreatePermissionCommand::class)]
final readonly class CreatePermissionCommandHandler
{
    public function __construct(
        private PermissionFactory $permissionFactory,
        private PermissionRepository $permissionRepository,
    ) {}

    public function __invoke(CreatePermissionCommand $createPermissionCommand): Result
    {
        $permission = $this->permissionFactory->create(
            $createPermissionCommand->name,
            $createPermissionCommand->description
        );

        $this->permissionRepository->savePermission($permission);

        return Result::success(new PermissionPayload($permission));
    }
}
