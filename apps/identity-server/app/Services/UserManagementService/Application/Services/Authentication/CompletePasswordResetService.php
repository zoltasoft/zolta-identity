<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\CompletePasswordReset\CompletePasswordResetCommand;
use App\Services\UserManagementService\Application\DTOs\Input\CompletePasswordResetDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AuthenticationMessageResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class CompletePasswordResetService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(CompletePasswordResetDTO $dto): AuthenticationMessageResponseDTO
    {
        $this->applicationService
            ->runAndCapture(CompletePasswordResetCommand::class, [
                'email' => $dto->email,
                'token' => $dto->token,
                'password' => $dto->password,
            ])
            ->getOrFail();

        return new AuthenticationMessageResponseDTO('Your password has been reset.');
    }
}
