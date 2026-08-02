<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromUser;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\UserId;

#[HandlesCommand(RevokePermissionFromUserCommand::class)]
final readonly class RevokePermissionFromUserCommandHandler
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(RevokePermissionFromUserCommand $revokePermissionFromUserCommand): Result
    {
        $permissionId = $revokePermissionFromUserCommand->permissionId instanceof PermissionId
            ? $revokePermissionFromUserCommand->permissionId
            : new PermissionId((string) $revokePermissionFromUserCommand->permissionId);

        $userId = $revokePermissionFromUserCommand->userId instanceof UserId
            ? $revokePermissionFromUserCommand->userId
            : new UserId((string) $revokePermissionFromUserCommand->userId);

        $permission = $this->permissionRepository->findPermissionById($permissionId);
        if ($permission === null) {
            return Result::failure(new RuntimeException('Permission not found'));
        }

        $permission->revokeFromUser($userId);

        $this->permissionRepository->updatePermission($permission);

        return Result::success(new PermissionPayload($permission));
    }
}
