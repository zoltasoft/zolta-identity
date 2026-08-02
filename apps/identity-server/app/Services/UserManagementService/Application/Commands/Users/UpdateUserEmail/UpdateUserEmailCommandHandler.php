<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateUserEmail;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(UpdateUserEmailCommand::class)]
final readonly class UpdateUserEmailCommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(UpdateUserEmailCommand $updateUserEmailCommand): Result
    {
        $user = $this->userRepository->findUserById($updateUserEmailCommand->id);
        $user->changeEmail($updateUserEmailCommand->email);
        $this->userRepository->updateUser($user);

        return Result::success(new UserPayload($user));
    }
}
