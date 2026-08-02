<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\DTOs\Input\ListAccountSessionsDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AccountSessionCollectionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Authentication\ListAccountSessions\ListAccountSessionsQuery;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ListAccountSessionsService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ListAccountSessionsDTO $dto): AccountSessionCollectionResponseDTO
    {
        ['sessions' => $sessions] = $this->applicationService
            ->runAndCapture(ListAccountSessionsQuery::class, [
                'userId' => new UserId($dto->userId),
                'currentTokenId' => $dto->currentTokenId,
            ])
            ->getOrFail();

        return new AccountSessionCollectionResponseDTO($sessions);
    }
}
