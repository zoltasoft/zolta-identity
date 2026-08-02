<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Factories;

use App\Services\UserManagementService\Domain\Aggregates\Role;
use DateTimeImmutable;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\RoleName;
use Zolta\Domain\ValueObjects\UserId;

final class RoleFactory
{
    /**
     * Create a new Role aggregate.
     */
    public function create(
        RoleName $roleName,
        ?Description $description = null,
        array $permissions = []
    ): Role {
        return Role::create($roleName, $description, $permissions);
    }

    /**
     * Restore existing role aggregate.
     */
    public function restore(
        RoleId $roleId,
        RoleName $roleName,
        ?Description $description = null,
        array $permissions = [],
        array $userIds = [],
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): Role {
        return Role::restore($roleId, $roleName, $description, $permissions, $userIds, $createdAt, $updatedAt);
    }

    /**
     * Restore from DB row and permission rows (usage: mapper).
     *
     * expected role row keys: id, role (role name string) or name.
     *
     * @param  array<int,array|mixed>  $permissionRows
     * @param  array<int,array|mixed>  $userRows
     */
    public function restoreFromRow(
        array $row,
        array $permissionRows = [],
        ?PermissionFactory $permissionFactory = null,
        array $userRows = []
    ): Role {
        $roleId = new RoleId((string) $row['id']);
        $roleName = RoleName::resolve(['value' => (string) ($row['role'] ?? $row['name'] ?? 'User')]);

        $description = null;
        if (! empty($row['description'])) {
            $description = new Description((string) $row['description']);
        }

        $permissions = [];
        if ($permissionFactory !== null) {
            foreach ($permissionRows as $permissionRow) {
                $permissionData = is_array($permissionRow) ? $permissionRow : (method_exists($permissionRow, 'toArray') ? $permissionRow->toArray() : []);
                if (! empty($permissionData)) {
                    $permissions[] = $permissionFactory->restoreFromRow($permissionData);
                }
            }
        }

        $userIds = [];
        foreach ($userRows as $userRow) {
            $userIdValue = null;
            if (is_array($userRow)) {
                $userIdValue = $userRow['id'] ?? null;
            } elseif (is_object($userRow) && method_exists($userRow, 'getAttribute')) {
                $userIdValue = $userRow->getAttribute('id');
            } elseif (is_scalar($userRow)) {
                $userIdValue = $userRow;
            }

            if ($userIdValue !== null) {
                $userIds[] = new UserId((string) $userIdValue);
            }
        }

        $createdAt = ! empty($row['created_at']) ? new DateTimeImmutable((string) $row['created_at']) : null;
        $updatedAt = ! empty($row['updated_at']) ? new DateTimeImmutable((string) $row['updated_at']) : null;

        return $this->restore($roleId, $roleName, $description, $permissions, $userIds, $createdAt, $updatedAt);
    }

    public function getDefaultRoleName(): RoleName
    {
        return RoleName::resolve(['value' => 'User']);
    }

    public function getDefaultRole(): Role
    {
        return $this->create($this->getDefaultRoleName(), null, []);
    }
}
