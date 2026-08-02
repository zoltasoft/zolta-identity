<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Users\GetUserById;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GetUserByIdQuery::class)]
final class GetUserByIdQueryHandler
{
    public function __invoke(GetUserByIdQuery $getUserByIdQuery, UserRepository $userRepository, QueryOptionsFactory $queryOptionsFactory): Option
    {
        $queryOptions = $queryOptionsFactory->make($getUserByIdQuery->options);
        $user = $userRepository->findUserById($getUserByIdQuery->id, $queryOptions);

        if ($user === null) {
            return Option::none();
        }

        return Option::some(new UserPayload($user));
    }
}
