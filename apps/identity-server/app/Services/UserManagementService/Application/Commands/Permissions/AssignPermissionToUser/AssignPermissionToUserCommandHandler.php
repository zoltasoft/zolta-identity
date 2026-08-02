<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToUser;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\UserId;

#[HandlesCommand(AssignPermissionToUserCommand::class)]
final readonly class AssignPermissionToUserCommandHandler
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(AssignPermissionToUserCommand $assignPermissionToUserCommand): Result
    {
        $permissionId = $assignPermissionToUserCommand->permissionId instanceof PermissionId
            ? $assignPermissionToUserCommand->permissionId
            : new PermissionId((string) $assignPermissionToUserCommand->permissionId);

        $userId = $assignPermissionToUserCommand->userId instanceof UserId
            ? $assignPermissionToUserCommand->userId
            : new UserId((string) $assignPermissionToUserCommand->userId);

        $permission = $this->permissionRepository->findPermissionById($permissionId);
        if ($permission === null) {
            return Result::failure(new RuntimeException('Permission not found'));
        }

        $permission->assignToUser($userId);

        $this->permissionRepository->updatePermission($permission);

        return Result::success(new PermissionPayload($permission));
    }
}
