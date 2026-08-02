<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use Zolta\Domain\Repositories\Query\AbstractQueryOptions;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\PermissionName;

interface PermissionRepository
{
    public function findPermissionById(PermissionId $permissionId): ?Permission;

    public function findPermissionByName(PermissionName $permissionName): ?Permission;

    /**
     * @return iterable<Permission>
     */
    public function getAllPermissions(AbstractQueryOptions $queryOptions): iterable;

    public function savePermission(Permission $permission): void;

    public function updatePermission(Permission $permission): void;

    public function deletePermission(Permission $permission): void;
}
