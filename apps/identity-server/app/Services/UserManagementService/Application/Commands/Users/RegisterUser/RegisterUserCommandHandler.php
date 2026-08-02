<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\RegisterUser;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Factories\UserFactory;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(RegisterUserCommand::class)]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserFactory $userFactory
    ) {}

    public function __invoke(RegisterUserCommand $registerUserCommand): Result
    {
        $registerData = [
            'username' => $registerUserCommand->username->get('username'),
            'email' => $registerUserCommand->email->get('address'),
            'password' => $registerUserCommand->password,
            'terms' => $registerUserCommand->terms,
        ];
        $user = $this->userFactory->create($registerData, $registerUserCommand->role);
        $this->userRepository->saveUser($user);

        return Result::success(new UserPayload($user));
    }
}
