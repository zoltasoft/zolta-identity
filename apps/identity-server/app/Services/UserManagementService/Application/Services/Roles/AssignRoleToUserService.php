<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\Commands\Roles\AssignRoleToUser\AssignRoleToUserCommand;
use App\Services\UserManagementService\Application\DTOs\Input\AssignRoleToUserDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RoleResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class AssignRoleToUserService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(AssignRoleToUserDTO $assignRoleToUserDTO): RoleResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'roleId' => $assignRoleToUserDTO->roleId,
            'userId' => $assignRoleToUserDTO->userId,
        ]]);

        $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $assignRoleToUserDTO->roleId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $this->applicationService->runAndCapture(GetUserByIdQuery::class, ['id' => $assignRoleToUserDTO->userId])
            ->getOrFail();

        $this->applicationService->runAndCapture(AssignRoleToUserCommand::class, [
            'roleId' => $assignRoleToUserDTO->roleId,
            'userId' => $assignRoleToUserDTO->userId,
        ])->getOrFail();

        $roleData = $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $assignRoleToUserDTO->roleId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load role after assignment'));

        return RoleResponseDTO::fromDomain($roleData['role']);
    }
}
