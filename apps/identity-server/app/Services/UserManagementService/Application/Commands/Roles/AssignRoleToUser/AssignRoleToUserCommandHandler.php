<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\AssignRoleToUser;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(AssignRoleToUserCommand::class)]
final readonly class AssignRoleToUserCommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(AssignRoleToUserCommand $assignRoleToUserCommand): Result
    {
        $user = $this->userRepository->findUserById($assignRoleToUserCommand->userId);
        if ($user === null) {
            return Result::failure(new RuntimeException('User not found'));
        }

        $user->assignRole($assignRoleToUserCommand->roleId);

        $this->userRepository->updateUser($user);

        return Result::success(new UserPayload($user));
    }
}
