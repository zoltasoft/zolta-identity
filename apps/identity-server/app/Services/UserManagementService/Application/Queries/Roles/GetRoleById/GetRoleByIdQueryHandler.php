<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Roles\GetRoleById;

use App\Services\UserManagementService\Application\Payloads\Roles\RolePayload;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GetRoleByIdQuery::class)]
final readonly class GetRoleByIdQueryHandler
{
    public function __construct(
        private RoleRepository $roleRepository,
        private QueryOptionsFactory $queryOptionsFactory
    ) {}

    public function __invoke(GetRoleByIdQuery $getRoleByIdQuery): Option
    {
        $queryOptions = $this->queryOptionsFactory->make($getRoleByIdQuery->options);
        $role = $this->roleRepository->findRoleById($getRoleByIdQuery->id, $queryOptions);

        if ($role === null) {
            return Option::none();
        }

        return Option::some(new RolePayload($role));
    }
}
