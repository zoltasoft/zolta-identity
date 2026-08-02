<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\VerifyEmail\VerifyEmailCommand;
use App\Services\UserManagementService\Application\DTOs\Input\VerifyEmailDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AuthenticationMessageResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class VerifyEmailService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(VerifyEmailDTO $dto): AuthenticationMessageResponseDTO
    {
        $this->applicationService
            ->runAndCapture(VerifyEmailCommand::class, [
                'userId' => new UserId($dto->userId),
                'code' => $dto->code,
            ])
            ->getOrFail();

        return new AuthenticationMessageResponseDTO('Your email address has been verified.');
    }
}
