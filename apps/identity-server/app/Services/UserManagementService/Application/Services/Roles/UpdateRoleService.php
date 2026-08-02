<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToRole\AssignPermissionToRoleCommand;
use App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromRole\RevokePermissionFromRoleCommand;
use App\Services\UserManagementService\Application\Commands\Roles\UpdateRole\UpdateRoleCommand;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateRoleDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RoleResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class UpdateRoleService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(UpdateRoleDTO $updateRoleDTO): RoleResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'roleId' => $updateRoleDTO->roleId,
            'name' => $updateRoleDTO->name,
            'description' => $updateRoleDTO->description,
            'permissionIds' => $updateRoleDTO->permissionIds,
        ]]);

        $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $updateRoleDTO->roleId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $this->applicationService->runAndCapture(UpdateRoleCommand::class, [
            'roleId' => $updateRoleDTO->roleId,
            'name' => $updateRoleDTO->name,
            'description' => $updateRoleDTO->description,
        ])->getOrFail();

        $roleData = $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $updateRoleDTO->roleId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load role after update'));

        $role = $roleData['role'];

        if ($updateRoleDTO->permissionIds !== null) {
            $existingPermissionIds = array_map(
                static fn ($permission) => $permission->getId()->get('value'),
                $role->getPermissions()
            );

            $desiredPermissionIds = array_map(strval(...), $updateRoleDTO->permissionIds);

            $toAttach = array_diff($desiredPermissionIds, $existingPermissionIds);
            $toDetach = array_diff($existingPermissionIds, $desiredPermissionIds);

            foreach ($toAttach as $permissionId) {
                $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, ['permissionId' => $permissionId])
                    ->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

                $this->applicationService->runAndCapture(AssignPermissionToRoleCommand::class, [
                    'permissionId' => $permissionId,
                    'roleId' => $updateRoleDTO->roleId,
                ]);
            }

            foreach ($toDetach as $permissionId) {
                $this->applicationService->runAndCapture(RevokePermissionFromRoleCommand::class, [
                    'permissionId' => $permissionId,
                    'roleId' => $updateRoleDTO->roleId,
                ]);
            }

            $roleData = $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $updateRoleDTO->roleId])
                ->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load role after permission sync'));

            $role = $roleData['role'];
        }

        return RoleResponseDTO::fromDomain($role);
    }
}
