<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\Commands\Permissions\DeletePermission\DeletePermissionCommand;
use App\Services\UserManagementService\Application\DTOs\Input\DeletePermissionDTO;
use App\Services\UserManagementService\Application\DTOs\Output\DeletePermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class DeletePermissionService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(DeletePermissionDTO $deletePermissionDTO): DeletePermissionResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'permissionId' => $deletePermissionDTO->permissionId,
        ]]);

        $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $deletePermissionDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

        $result = $this->applicationService->runAndCapture(DeletePermissionCommand::class, [
            'permissionId' => $deletePermissionDTO->permissionId,
        ]);

        $result->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to delete permission'));

        return new DeletePermissionResponseDTO(
            permissionId: $deletePermissionDTO->permissionId,
            message: 'Permission deleted successfully.'
        );
    }
}
