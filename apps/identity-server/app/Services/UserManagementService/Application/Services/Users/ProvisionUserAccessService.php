<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\Commands\Users\ProvisionUserAccess\ProvisionUserAccessCommand;
use App\Services\UserManagementService\Application\DTOs\Input\ProvisionUserAccessDTO;
use App\Services\UserManagementService\Application\DTOs\Output\ProvisionUserAccessResponseDTO;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ProvisionUserAccessService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ProvisionUserAccessDTO $provisionUserAccessDTO): ProvisionUserAccessResponseDTO
    {
        $this->applicationService->capture([
            'input' => [
                'userId' => $provisionUserAccessDTO->userId,
                'roleId' => $provisionUserAccessDTO->roleId,
                'permissionIds' => $provisionUserAccessDTO->permissionIds,
                'attachPermissionsToRole' => $provisionUserAccessDTO->attachPermissionsToRole,
            ],
        ]);

        $provisioned = $this->applicationService->runAndCapture(ProvisionUserAccessCommand::class, [
            'userId' => $provisionUserAccessDTO->userId,
            'roleId' => $provisionUserAccessDTO->roleId,
            'permissionIds' => $provisionUserAccessDTO->permissionIds,
            'attachPermissionsToRole' => $provisionUserAccessDTO->attachPermissionsToRole,
        ])->getOrFail();

        // Snapshot the refreshed user for consumers that rely on query projections.
        $snapshot = $this->applicationService->runAndCapture(GetUserByIdQuery::class, [
            'id' => $provisionUserAccessDTO->userId,
            'include' => ['permissions', 'role', 'roles'],
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to reload user after provisioning'));

        $user = $provisioned['user'] ?? $snapshot['user'] ?? null;
        $role = $provisioned['role'] ?? null;
        $permissions = $provisioned['permissions'] ?? [];

        if ($user === null || $role === null) {
            throw new RuntimeException('Provision user access command returned an unexpected payload.');
        }

        $captureLog = array_keys($this->applicationService->getCaptured());

        return ProvisionUserAccessResponseDTO::fromDomain($user, $role, $permissions, $captureLog);
    }
}
