<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Roles\ListRoles;

use App\Services\UserManagementService\Application\Payloads\Roles\RoleCollectionPayload;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ListRolesQuery::class)]
final readonly class ListRolesQueryHandler
{
    public function __construct(
        private RoleRepository $roleRepository,
        private QueryOptionsFactory $queryOptionsFactory
    ) {}

    public function __invoke(ListRolesQuery $listRolesQuery): Option
    {
        $queryOptions = $this->queryOptionsFactory->make($listRolesQuery->options);
        $roles = $this->roleRepository->getAllRoles($queryOptions);

        return Option::some(new RoleCollectionPayload(iterator_to_array($roles)));
    }
}
