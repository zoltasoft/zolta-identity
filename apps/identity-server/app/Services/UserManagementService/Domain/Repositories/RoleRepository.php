<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\Role;
use Zolta\Domain\Repositories\Query\AbstractQueryOptions;
use Zolta\Domain\ValueObjects\Pagination;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\RoleName;

interface RoleRepository
{
    public function findRoleById(RoleId $roleId, ?AbstractQueryOptions $queryOptions = null): ?Role;

    public function findRoleByName(RoleName $roleName, ?AbstractQueryOptions $queryOptions = null): ?Role;

    public function saveRole(Role $role): void;

    public function updateRole(Role $role): void;

    public function deleteRole(Role $role): void;

    /**
     * Optionally return all roles (either array or pagination wrapper).
     *
     * @return iterable<Role>
     */
    public function getAllRoles(AbstractQueryOptions $queryOptions): iterable;

    /**
     * @return iterable<Role>
     */
    public function findRolesWithPermissions(): iterable;

    public function findRolesPaginated(AbstractQueryOptions $queryOptions): Pagination;
}
