<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\VerifyEmail;

use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Exceptions\ValidationException;

#[HandlesCommand(VerifyEmailCommand::class)]
final readonly class VerifyEmailCommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(VerifyEmailCommand $command): Result
    {
        $user = $this->userRepository->findUserById($command->userId);

        if (! $user) {
            throw new UserNotFoundException;
        }

        if ($user->getEmail()->isVerified()) {
            return Result::success();
        }

        if (! $user->verifyEmail($command->code)) {
            throw new ValidationException([
                'code' => ['The verification code is invalid or has expired.'],
            ]);
        }

        $this->userRepository->updateUser($user);

        return Result::success();
    }
}
