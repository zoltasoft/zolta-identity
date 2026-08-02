<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\Commands\Roles\RevokeRoleFromUser\RevokeRoleFromUserCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RevokeRoleFromUserDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RoleResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleByName\GetRoleByNameQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RevokeRoleFromUserService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RevokeRoleFromUserDTO $revokeRoleFromUserDTO): RoleResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'roleId' => $revokeRoleFromUserDTO->roleId,
            'userId' => $revokeRoleFromUserDTO->userId,
        ]]);

        $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $revokeRoleFromUserDTO->roleId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $this->applicationService->runAndCapture(GetUserByIdQuery::class, ['id' => $revokeRoleFromUserDTO->userId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('User not found'));

        $fallbackRoleData = $this->applicationService->runAndCapture(GetRoleByNameQuery::class, ['name' => 'User'])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Default role not found'));

        $fallbackRole = $fallbackRoleData['role'];

        $this->applicationService->runAndCapture(RevokeRoleFromUserCommand::class, [
            'roleId' => $revokeRoleFromUserDTO->roleId,
            'userId' => $revokeRoleFromUserDTO->userId,
            'fallbackRoleId' => $fallbackRole->getId()->get('value'),
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to revoke role from user'));

        $updatedRole = $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $fallbackRole->getId()->get('value')])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load fallback role'));

        return RoleResponseDTO::fromDomain($updatedRole['role']);
    }
}
