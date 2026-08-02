<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\DTOs\Input\ListRolesDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RoleCollectionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\ListRoles\ListRolesQuery;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ListRolesService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ListRolesDTO $listRolesDTO): RoleCollectionResponseDTO
    {
        ['roles' => $roles] = $this->applicationService->runAndCapture(ListRolesQuery::class, [
            'options' => $listRolesDTO->options,
        ])->getOrFail();

        return RoleCollectionResponseDTO::fromDomain($roles);
    }
}
