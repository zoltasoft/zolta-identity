<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\DeleteUserByEmail;

use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(DeleteUserByEmailCommand::class)]
final readonly class DeleteUserByEmailCommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(DeleteUserByEmailCommand $deleteUserByEmailCommand): Result
    {
        $user = $this->userRepository->findUserByEmail($deleteUserByEmailCommand->email);

        if ($user !== null) {
            $this->userRepository->deleteUser($user);
        }

        return Result::success(['message' => 'Deleted by email']);
    }
}
