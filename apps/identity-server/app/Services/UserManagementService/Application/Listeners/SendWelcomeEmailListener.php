<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Listeners;

use App\Services\UserManagementService\Application\Contracts\SendWelcomeMessageServiceInterface;
use App\Services\UserManagementService\Application\DTOs\Input\SendWelcomeMessageDTO;
use App\Services\UserManagementService\Application\DTOs\Output\SendWelcomeMessageResponseDTO;
use App\Services\UserManagementService\Application\Services\Authentication\SendWelcomeMessageService;
use App\Services\UserManagementService\Domain\Events\UserRegisteredEvent;
use Psr\Log\LoggerInterface;
use Zolta\Domain\Events\Contracts\EventInterface;

final readonly class SendWelcomeEmailListener implements SendWelcomeMessageServiceInterface
{
    public function __construct(
        private SendWelcomeMessageService $sendWelcomeMessageService,
        private LoggerInterface $logger,
    ) {}

    public function expectedEventType(): string
    {
        return UserRegisteredEvent::class;
    }

    /**
     * @param  UserRegisteredEvent  $event
     */
    public function handleEvent(EventInterface $event): void
    {
        /** @var SendWelcomeMessageResponseDTO $sendWelcomeMessageResponseDTO */
        $sendWelcomeMessageResponseDTO = ($this->sendWelcomeMessageService)(new SendWelcomeMessageDTO(
            userId: $event->getUserId()->get('value')
        ));
        $this->logger->info('welcome email dispatched', ['success' => $sendWelcomeMessageResponseDTO->success]);
    }
}
