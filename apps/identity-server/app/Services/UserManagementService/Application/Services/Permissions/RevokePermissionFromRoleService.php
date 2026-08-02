<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromRole\RevokePermissionFromRoleCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RevokePermissionFromRoleDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RevokePermissionFromRoleService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RevokePermissionFromRoleDTO $revokePermissionFromRoleDTO): PermissionResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'permissionId' => $revokePermissionFromRoleDTO->permissionId,
            'roleId' => $revokePermissionFromRoleDTO->roleId,
        ]]);

        $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $revokePermissionFromRoleDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

        $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $revokePermissionFromRoleDTO->roleId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $this->applicationService->runAndCapture(RevokePermissionFromRoleCommand::class, [
            'permissionId' => $revokePermissionFromRoleDTO->permissionId,
            'roleId' => $revokePermissionFromRoleDTO->roleId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to revoke permission from role'));

        $refreshed = $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $revokePermissionFromRoleDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load permission after revocation'));

        return PermissionResponseDTO::fromDomain($refreshed['permission']);
    }
}
