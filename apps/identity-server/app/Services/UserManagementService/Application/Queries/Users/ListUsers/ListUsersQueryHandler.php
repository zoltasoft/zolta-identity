<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Users\ListUsers;

use App\Services\UserManagementService\Application\Payloads\Users\UserCollectionPayload;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ListUsersQuery::class)]
final readonly class ListUsersQueryHandler
{
    public function __construct(public UserRepository $userRepository, private QueryOptionsFactory $queryOptionsFactory) {}

    public function __invoke(
        ListUsersQuery $listUsersQuery,
    ): Option {
        $queryOptions = $this->queryOptionsFactory->make($listUsersQuery->options);
        $userPaginationCollection = $this->userRepository->findAllUsers($queryOptions);

        return Option::some(new UserCollectionPayload($userPaginationCollection));
    }
}
