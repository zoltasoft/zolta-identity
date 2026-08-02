<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromUser\RevokePermissionFromUserCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RevokePermissionFromUserDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RevokePermissionFromUserService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RevokePermissionFromUserDTO $revokePermissionFromUserDTO): PermissionResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'permissionId' => $revokePermissionFromUserDTO->permissionId,
            'userId' => $revokePermissionFromUserDTO->userId,
        ]]);

        $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $revokePermissionFromUserDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

        $this->applicationService->runAndCapture(GetUserByIdQuery::class, ['id' => $revokePermissionFromUserDTO->userId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('User not found'));

        $this->applicationService->runAndCapture(RevokePermissionFromUserCommand::class, [
            'permissionId' => $revokePermissionFromUserDTO->permissionId,
            'userId' => $revokePermissionFromUserDTO->userId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to revoke permission from user'));

        $refreshed = $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $revokePermissionFromUserDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load permission after revocation'));

        return PermissionResponseDTO::fromDomain($refreshed['permission']);
    }
}
