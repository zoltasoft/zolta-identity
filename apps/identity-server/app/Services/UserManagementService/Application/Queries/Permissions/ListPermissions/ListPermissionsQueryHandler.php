<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Permissions\ListPermissions;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionCollectionPayload;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ListPermissionsQuery::class)]
final readonly class ListPermissionsQueryHandler
{
    public function __construct(
        public PermissionRepository $permissionRepository,
        private QueryOptionsFactory $queryOptionsFactory
    ) {}

    public function __invoke(ListPermissionsQuery $listPermissionsQuery): Option
    {
        $queryOptions = $this->queryOptionsFactory->make($listPermissionsQuery->options);
        $permissions = $this->permissionRepository->getAllPermissions($queryOptions);

        return Option::some(new PermissionCollectionPayload(iterator_to_array($permissions)));
    }
}
