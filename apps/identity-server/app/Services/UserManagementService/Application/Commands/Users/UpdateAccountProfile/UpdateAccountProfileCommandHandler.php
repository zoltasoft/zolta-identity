<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateAccountProfile;

use App\Services\UserManagementService\Application\Contracts\MailerService;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(UpdateAccountProfileCommand::class)]
final readonly class UpdateAccountProfileCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerService $mailer,
    ) {}

    public function __invoke(UpdateAccountProfileCommand $updateAccountProfileCommand): Result
    {
        $user = $this->userRepository->findUserById($updateAccountProfileCommand->userId);
        if ($user === null) {
            return Result::failure(new RuntimeException('User not found.'));
        }

        $emailChanged = $user->getEmail()->get('address') !== $updateAccountProfileCommand->email->get('address');

        $user->changeUsername($updateAccountProfileCommand->username);
        $user->changeEmail($updateAccountProfileCommand->email);
        $user->setProfilePicture($updateAccountProfileCommand->profilePicture);

        $this->userRepository->updateUser($user);

        if ($emailChanged && $user->getVerificationCode()) {
            $this->mailer->sendEmailVerificationCode(
                $user->getEmail(),
                $user->getUsername(),
                $user->getVerificationCode()
            );
        }

        return Result::success([
            'user_id' => $user->getId()->get('value'),
            'updated' => true,
        ]);
    }
}
