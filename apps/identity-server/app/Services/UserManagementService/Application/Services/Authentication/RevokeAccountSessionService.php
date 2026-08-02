<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\RevokeAccountSession\RevokeAccountSessionCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RevokeAccountSessionDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AuthenticationMessageResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RevokeAccountSessionService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RevokeAccountSessionDTO $dto): AuthenticationMessageResponseDTO
    {
        $this->applicationService
            ->runAndCapture(RevokeAccountSessionCommand::class, [
                'userId' => new UserId($dto->userId),
                'tokenId' => $dto->sessionId,
            ])
            ->getOrFail();

        return new AuthenticationMessageResponseDTO('The session has been signed out.');
    }
}
