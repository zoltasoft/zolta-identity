<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\DeleteUserById;

use App\Services\UserManagementService\Application\Contracts\AccountDataEraserInterface;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(DeleteUserByIdCommand::class)]
final readonly class DeleteUserByIdCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AccountDataEraserInterface $accountDataEraser,
    ) {}

    public function __invoke(DeleteUserByIdCommand $deleteUserByIdCommand): Result
    {
        $user = $this->userRepository->findUserById($deleteUserByIdCommand->id);

        if ($user !== null) {
            $this->accountDataEraser->erase(
                $user->getId(),
                (string) $user->getEmail()->get('address')
            );
            $this->userRepository->deleteUser($user);
        }

        return Result::success(['message' => 'The user has been deleted successfully!']);
    }
}
