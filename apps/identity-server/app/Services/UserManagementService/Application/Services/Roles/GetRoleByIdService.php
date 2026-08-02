<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\DTOs\Input\GetRoleByIdDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RoleResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class GetRoleByIdService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetRoleByIdDTO $getRoleByIdDTO): RoleResponseDTO
    {
        $this->applicationService->capture(['input' => ['id' => $getRoleByIdDTO->id, 'options' => $getRoleByIdDTO->options]]);

        $result = $this->applicationService->runAndCapture(GetRoleByIdQuery::class, [
            'id' => map('input.id'),
            'options' => map('input.options'),
        ]);

        $data = $result->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $role = $data['role'] ?? null;
        if ($role === null) {
            throw new RuntimeException('Role not found');
        }

        return RoleResponseDTO::fromDomain($role);
    }
}
