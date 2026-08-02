<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\SendWelcomeMessage\SendWelcomeMessageCommand;
use App\Services\UserManagementService\Application\DTOs\Input\SendWelcomeMessageDTO;
use App\Services\UserManagementService\Application\DTOs\Output\SendWelcomeMessageResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class SendWelcomeMessageService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(SendWelcomeMessageDTO $sendWelcomeMessageDTO): SendWelcomeMessageResponseDTO
    {

        $this->applicationService->runAndCapture(SendWelcomeMessageCommand::class, [
            'id' => $sendWelcomeMessageDTO->userId,
        ])->getOrFail();

        return $this->applicationService->response(
            map: [
                'success' => 'success',
                'message' => 'message',
            ],
            responseDto: SendWelcomeMessageResponseDTO::class
        );
    }
}
