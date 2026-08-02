<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\RequestPasswordReset\RequestPasswordResetCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RequestPasswordResetDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AuthenticationMessageResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RequestPasswordResetService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RequestPasswordResetDTO $dto): AuthenticationMessageResponseDTO
    {
        $this->applicationService
            ->runAndCapture(RequestPasswordResetCommand::class, ['email' => $dto->email])
            ->getOrFail();

        return new AuthenticationMessageResponseDTO(
            'If an account exists for that email, a password reset link has been sent.'
        );
    }
}
