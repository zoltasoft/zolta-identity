<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Permissions;

use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToRole\AssignPermissionToRoleCommand;
use App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToUser\AssignPermissionToUserCommand;
use App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromRole\RevokePermissionFromRoleCommand;
use App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromUser\RevokePermissionFromUserCommand;
use App\Services\UserManagementService\Application\Commands\Permissions\UpdatePermission\UpdatePermissionCommand;
use App\Services\UserManagementService\Application\DTOs\Input\UpdatePermissionDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PermissionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById\GetPermissionByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Option;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class UpdatePermissionService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(UpdatePermissionDTO $updatePermissionDTO): PermissionResponseDTO
    {
        return $this->applicationService->transactional(function (ApplicationService $applicationService) use ($updatePermissionDTO): PermissionResponseDTO {
            // --- 1️⃣ Capture input state ---
            $applicationService->capture(['input' => [
                'permissionId' => $updatePermissionDTO->permissionId,
                'name' => $updatePermissionDTO->name,
                'description' => $updatePermissionDTO->description,
                'roleIds' => $updatePermissionDTO->roleIds,
                'userIds' => $updatePermissionDTO->userIds,
            ]]);

            // --- 2️⃣ Update permission core fields ---
            $applicationService->runAndCapture(UpdatePermissionCommand::class, [
                'permissionId' => $updatePermissionDTO->permissionId,
                'name' => $updatePermissionDTO->name,
                'description' => $updatePermissionDTO->description,
            ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to update permission'));

            // --- 3️⃣ Reload updated permission ---
            $permissionDto = $applicationService->runAndCapture(GetPermissionByIdQuery::class, [
                'permissionId' => $updatePermissionDTO->permissionId,
            ])->getOrFail(fn (): RuntimeException => new RuntimeException('Permission not found after update'));

            $permission = $permissionDto['permission'];
            $permissionId = $permission->getId()->get('value');

            // --- 4️⃣ Sync roles and users ---
            $this->syncRelations(
                $applicationService,
                $updatePermissionDTO->roleIds,
                $permission->getRoleIds(),
                fn ($id): Option|\Zolta\Cqrs\Services\Result => $applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => $id]),
                fn ($pid, $id): Option|\Zolta\Cqrs\Services\Result => $applicationService->runAndCapture(AssignPermissionToRoleCommand::class, ['permissionId' => $pid, 'roleId' => $id]),
                fn ($pid, $id): Option|\Zolta\Cqrs\Services\Result => $applicationService->runAndCapture(RevokePermissionFromRoleCommand::class, ['permissionId' => $pid, 'roleId' => $id]),
                'role',
                $permissionId
            );

            $this->syncRelations(
                $applicationService,
                $updatePermissionDTO->userIds,
                $permission->getUserIds(),
                fn ($id): Option|\Zolta\Cqrs\Services\Result => $applicationService->runAndCapture(GetUserByIdQuery::class, ['id' => $id]),
                fn ($pid, $id): Option|\Zolta\Cqrs\Services\Result => $applicationService->runAndCapture(AssignPermissionToUserCommand::class, ['permissionId' => $pid, 'userId' => $id]),
                fn ($pid, $id): Option|\Zolta\Cqrs\Services\Result => $applicationService->runAndCapture(RevokePermissionFromUserCommand::class, ['permissionId' => $pid, 'userId' => $id]),
                'user',
                $permissionId
            );

            // --- 5️⃣ Final refreshed DTO ---
            $refreshed = $applicationService->runAndCapture(GetPermissionByIdQuery::class, [
                'permissionId' => $updatePermissionDTO->permissionId,
            ])->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to load permission after update'));

            return PermissionResponseDTO::fromDomain($refreshed['permission']);
        });
    }

    /**
     * Synchronize attached and detached relations for roles or users.
     *
     * @param  array<string>|null  $desiredIds
     */
    private function syncRelations(
        ApplicationService $applicationService,
        ?array $desiredIds,
        iterable $existingObjects,
        callable $validateCallback,
        callable $attachCallback,
        callable $detachCallback,
        string $entityLabel,
        string $permissionId
    ): void {
        if ($desiredIds === null) {
            return;
        }

        $existingIds = array_map(static fn ($obj) => $obj->get('value'), $existingObjects);
        $desiredIds = array_map(strval(...), $desiredIds);

        $toAttach = array_diff($desiredIds, $existingIds);
        $toDetach = array_diff($existingIds, $desiredIds);

        foreach ($toAttach as $id) {
            $validateCallback($id)->getOrFail(fn (): RuntimeException => new RuntimeException(ucfirst($entityLabel)." [$id] not found"));
            $attachCallback($permissionId, $id)->getOrFail(fn (): RuntimeException => new RuntimeException("Unable to assign permission to $entityLabel [$id]"));
        }

        foreach ($toDetach as $id) {
            $detachCallback($permissionId, $id)->getOrFail(fn (): RuntimeException => new RuntimeException("Unable to revoke permission from $entityLabel [$id]"));
        }
    }
}
