<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\DTOs\Input\ListUsersDTO;
use App\Services\UserManagementService\Application\DTOs\Output\UserCollectionResponseDTO;
use App\Services\UserManagementService\Application\Queries\Users\ListUsers\ListUsersQuery;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ListUsersService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ListUsersDTO $listUsersDTO): UserCollectionResponseDTO
    {
        ['userPaginationCollection' => $userPaginationCollection] = $this->applicationService
            ->runAndCapture(ListUsersQuery::class, [
                'options' => $listUsersDTO->options,
            ])
            ->getOrFail();

        $userCollectionResponseDTO = UserCollectionResponseDTO::fromDomain(users: $userPaginationCollection->items, meta: [
            'total' => $userPaginationCollection->total,
            'perPage' => $userPaginationCollection->perPage,
            'currentPage' => $userPaginationCollection->currentPage,
            'lastPage' => $userPaginationCollection->lastPage,
        ]);

        return $userCollectionResponseDTO;
    }
}
