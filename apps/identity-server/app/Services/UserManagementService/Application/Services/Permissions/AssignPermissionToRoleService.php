<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToRole\AssignPermissionToRoleCommand;
use App\Services\UserManagementService\Application\DTOs\Input\AssignPermissionToRoleDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class AssignPermissionToRoleService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(AssignPermissionToRoleDTO $assignPermissionToRoleDTO): PermissionResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'permissionId' => $assignPermissionToRoleDTO->permissionId,
            'roleId' => $assignPermissionToRoleDTO->roleId,
        ]]);

        $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $assignPermissionToRoleDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

        $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $assignPermissionToRoleDTO->roleId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $this->applicationService->runAndCapture(AssignPermissionToRoleCommand::class, [
            'permissionId' => $assignPermissionToRoleDTO->permissionId,
            'roleId' => $assignPermissionToRoleDTO->roleId,
        ])->getOrFail();

        $refreshed = $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $assignPermissionToRoleDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load permission after assignment'));

        return PermissionResponseDTO::fromDomain($refreshed['permission']);
    }
}
