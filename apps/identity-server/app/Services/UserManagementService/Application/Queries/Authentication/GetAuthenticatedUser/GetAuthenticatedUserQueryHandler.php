<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Authentication\GetAuthenticatedUser;

use App\Services\UserManagementService\Application\Contracts\AuthenticationServiceInterface;
use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GetAuthenticatedUserQuery::class)]
final readonly class GetAuthenticatedUserQueryHandler
{
    public function __construct(
        private AuthenticationServiceInterface $authenticationService,
        private QueryOptionsFactory $queryOptionsFactory
    ) {}

    public function __invoke(GetAuthenticatedUserQuery $getAuthenticatedUserQuery): Option
    {
        $queryOptions = $this->queryOptionsFactory->make([
            'include' => ['role', 'role.permissions'],
        ]);
        $user = $this->authenticationService->getAuthenticatedUser($queryOptions);

        if ($user === null) {
            return Option::none();
        }

        return Option::some(new UserPayload($user));
    }
}
