<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\DTOs\Input\ListPermissionsDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionCollectionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\ListPermissions\ListPermissionsQuery;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ListPermissionsService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ListPermissionsDTO $listPermissionsDTO): PermissionCollectionResponseDTO
    {

        ['permissions' => $permissions] = $this->applicationService->runAndCapture(ListPermissionsQuery::class, [
            'options' => $listPermissionsDTO->options,
        ])->getOrFail();

        return PermissionCollectionResponseDTO::fromDomain($permissions);
    }
}
