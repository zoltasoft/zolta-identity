<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToUser\AssignPermissionToUserCommand;
use App\Services\UserManagementService\Application\DTOs\Input\AssignPermissionToUserDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class AssignPermissionToUserService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(AssignPermissionToUserDTO $assignPermissionToUserDTO): PermissionResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'permissionId' => $assignPermissionToUserDTO->permissionId,
            'userId' => $assignPermissionToUserDTO->userId,
        ]]);

        $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $assignPermissionToUserDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found'));

        $this->applicationService->runAndCapture(GetUserByIdQuery::class, ['id' => $assignPermissionToUserDTO->userId])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('User not found'));

        $this->applicationService->runAndCapture(AssignPermissionToUserCommand::class, [
            'permissionId' => $assignPermissionToUserDTO->permissionId,
            'userId' => $assignPermissionToUserDTO->userId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to assign permission to user'));

        $refreshed = $this->applicationService->runAndCapture(GetPermissionByIdQuery::class, [
            'permissionId' => $assignPermissionToUserDTO->permissionId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load permission after assignment'));

        return PermissionResponseDTO::fromDomain($refreshed['permission']);
    }
}
