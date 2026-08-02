<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Users\GetUserByEmail;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GetUserByEmailQuery::class)]
final class GetUserByEmailQueryHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private QueryOptionsFactory $queryOptionsFactory
    ) {}

    public function __invoke(GetUserByEmailQuery $getUserByEmailQuery): Option
    {
        $queryOptions = $this->queryOptionsFactory->make($getUserByEmailQuery->include);
        $user = $this->userRepository->findUserByEmail($getUserByEmailQuery->email, $queryOptions);

        if ($user === null) {
            return Option::none();
        }

        return Option::some(new UserPayload($user));
    }
}
