<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\ResendEmailVerification;

use App\Services\UserManagementService\Application\Contracts\MailerService;
use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ResendEmailVerificationCommand::class)]
final readonly class ResendEmailVerificationCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerService $mailer,
    ) {}

    public function __invoke(ResendEmailVerificationCommand $command): Result
    {
        $user = $this->userRepository->findUserById($command->userId);

        if (! $user) {
            throw new UserNotFoundException;
        }

        if ($user->getEmail()->isVerified()) {
            return Result::success(new UserPayload($user));
        }

        $user->generateEmailVerificationCode();
        $this->userRepository->updateUser($user);
        $this->mailer->sendEmailVerificationCode(
            $user->getEmail(),
            $user->getUsername(),
            (string) $user->getVerificationCode()
        );

        return Result::success(new UserPayload($user));
    }
}
