<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\DTOs\Input\GetPermissionByIdDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class GetPermissionByIdService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetPermissionByIdDTO $getPermissionByIdDTO): PermissionResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'permissionId' => $getPermissionByIdDTO->permissionId,
        ]]);

        $result = $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => map('input.permissionId'),
        ]);

        $data = $result->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

        $permission = $data['permission'] ?? null;

        if ($permission === null) {
            throw new RuntimeException('Permission not found');
        }

        return PermissionResponseDTO::fromDomain($permission);
    }
}
