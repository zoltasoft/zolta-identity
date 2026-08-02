<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToRole\AssignPermissionToRoleCommand;
use App\Services\UserManagementService\Application\Commands\Roles\CreateRole\CreateRoleCommand;
use App\Services\UserManagementService\Application\DTOs\Input\CreateRoleDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RoleResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class CreateRoleService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(CreateRoleDTO $createRoleDTO): RoleResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'name' => $createRoleDTO->name,
            'description' => $createRoleDTO->description,
            'permissionIds' => $createRoleDTO->permissionIds,
        ]]);

        $this->applicationService->runAndCapture(CreateRoleCommand::class, [
            'name' => $createRoleDTO->name,
            'description' => $createRoleDTO->description,
        ])->getOrFail();

        $role = $this->applicationService->get('role');

        foreach ($createRoleDTO->permissionIds as $permissionId) {
            $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, ['permissionId' => $permissionId])
                ->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

            $this->applicationService->runAndCapture(AssignPermissionToRoleCommand::class, [
                'permissionId' => $permissionId,
                'roleId' => $role->getId()->get('value'),
            ]);
        }

        $refreshed = $this->applicationService->runAndCapture(GetRoleByIdQuery::class, [
            'id' => $role->getId()->get('value'),
        ])->getOrFail();

        return RoleResponseDTO::fromDomain($refreshed['role']);
    }
}
