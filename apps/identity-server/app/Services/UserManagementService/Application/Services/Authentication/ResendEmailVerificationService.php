<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\ResendEmailVerification\ResendEmailVerificationCommand;
use App\Services\UserManagementService\Application\DTOs\Input\ResendEmailVerificationDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AuthenticationMessageResponseDTO;
use App\Services\UserManagementService\Domain\Aggregates\User;
use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ResendEmailVerificationService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ResendEmailVerificationDTO $dto): AuthenticationMessageResponseDTO
    {
        $result = $this->applicationService
            ->runAndCapture(ResendEmailVerificationCommand::class, [
                'userId' => new UserId($dto->userId),
            ])
            ->getOrFail();

        $user = $result['user'] ?? null;

        if (! $user instanceof User) {
            throw new UserNotFoundException;
        }

        return new AuthenticationMessageResponseDTO(
            'A new verification code has been sent.',
            $user->getVerificationCode(),
        );
    }
}
