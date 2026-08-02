<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\DTOs\Input\GetRoleByNameDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RoleResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleByName\GetRoleByNameQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class GetRoleByNameService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetRoleByNameDTO $getRoleByNameDTO): RoleResponseDTO
    {
        $this->applicationService->capture(['input' => ['name' => $getRoleByNameDTO->name]]);

        $result = $this->applicationService->runAndCapture(GetRoleByNameQuery::class, [
            'name' => map('input.name'),
        ]);

        $data = $result->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $role = $data['role'] ?? null;
        if ($role === null) {
            throw new RuntimeException('Role not found');
        }

        return RoleResponseDTO::fromDomain($role);
    }
}
