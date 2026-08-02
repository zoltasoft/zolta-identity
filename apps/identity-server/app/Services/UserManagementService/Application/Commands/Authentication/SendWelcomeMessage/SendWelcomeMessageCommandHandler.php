<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SendWelcomeMessage;

use App\Services\UserManagementService\Application\Contracts\MailerService;
use App\Services\UserManagementService\Application\DTOs\External\MailerDTO;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(SendWelcomeMessageCommand::class)]
final readonly class SendWelcomeMessageCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerService $mailerService
    ) {}

    public function __invoke(SendWelcomeMessageCommand $sendWelcomeMessageCommand): Result
    {
        $user = $this->userRepository->findUserById($sendWelcomeMessageCommand->id);
        $this->mailerService->sendWelcomeUserMessage(new MailerDTO(
            companyName: 'Zolta',
            subject: 'Welcome message',
            email: $user->getEmail(),
            username: $user->getUsername(),
            verificationCode: $user->getVerificationCode(),
        ));

        return Result::success(['success' => true, 'message' => 'Email  sent']);
    }
}
