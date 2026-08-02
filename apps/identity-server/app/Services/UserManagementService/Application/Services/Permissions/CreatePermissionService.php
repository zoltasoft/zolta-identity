<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToRole\AssignPermissionToRoleCommand;
use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToUser\AssignPermissionToUserCommand;
use App\Services\UserManagementService\Application\Commands\Permissions\CreatePermission\CreatePermissionCommand;
use App\Services\UserManagementService\Application\DTOs\Input\CreatePermissionDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class CreatePermissionService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(CreatePermissionDTO $createPermissionDTO): PermissionResponseDTO
    {

        ['permission' => $permission] = $this->applicationService->runAndCapture(CreatePermissionCommand::class, [
            'name' => $createPermissionDTO->name,
            'description' => $createPermissionDTO->description,
        ])->getOrFail();

        foreach ($createPermissionDTO->roleIds as $roleId) {
            $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $roleId])
                ->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

            $this->applicationService->runAndCapture(AssignPermissionToRoleCommand::class, [
                'permissionId' => $permission->getId()->get('value'),
                'roleId' => $roleId,
            ])->getOrFail();

            $permission = $this->applicationService->get('permission');
        }

        foreach ($createPermissionDTO->userIds as $userId) {
            $this->applicationService->runAndCapture(GetUserByIdQuery::class, ['id' => $userId])
                ->getOrFail(fn (): RuntimeException => new RuntimeException('User not found'));

            $this->applicationService->runAndCapture(AssignPermissionToUserCommand::class, [
                'permissionId' => $permission->getId()->get('value'),
                'userId' => $userId,
            ])->getOrFail();

            $permission = $this->applicationService->get('permission');
        }

        $refreshed = $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $permission->getId()->get('value'),
        ])->getOrFail();

        return PermissionResponseDTO::fromDomain($refreshed['permission']);
    }
}
