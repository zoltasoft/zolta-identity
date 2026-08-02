<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\RevokeRoleFromUser;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

#[HandlesCommand(RevokeRoleFromUserCommand::class)]
final readonly class RevokeRoleFromUserCommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(RevokeRoleFromUserCommand $revokeRoleFromUserCommand): Result
    {
        $userId = $revokeRoleFromUserCommand->userId instanceof UserId
            ? $revokeRoleFromUserCommand->userId
            : new UserId((string) $revokeRoleFromUserCommand->userId);

        $user = $this->userRepository->findUserById($userId);
        if ($user === null) {
            return Result::failure(new RuntimeException('User not found'));
        }

        $roleId = $revokeRoleFromUserCommand->roleId instanceof RoleId
            ? $revokeRoleFromUserCommand->roleId
            : new RoleId((string) $revokeRoleFromUserCommand->roleId);

        if ($user->getRoleId()->get('value') !== $roleId->get('value')) {
            return Result::failure(new RuntimeException('Role mismatch for user'));
        }

        $fallbackRoleId = $revokeRoleFromUserCommand->fallbackRoleId instanceof RoleId
            ? $revokeRoleFromUserCommand->fallbackRoleId
            : new RoleId((string) $revokeRoleFromUserCommand->fallbackRoleId);

        $user->assignRole($fallbackRoleId);

        $this->userRepository->updateUser($user);

        return Result::success(new UserPayload($user));
    }
}
