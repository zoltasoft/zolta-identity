<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Factories;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use DateTimeImmutable;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\PermissionName;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

final class PermissionFactory
{
    public function create(PermissionName $permissionName, ?Description $description = null): Permission
    {
        return Permission::create($permissionName, $description);
    }

    public function restore(
        PermissionId $permissionId,
        PermissionName $permissionName,
        ?Description $description,
        array $roleIds = [],
        array $userIds = [],
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): Permission {
        return Permission::restore(
            $permissionId,
            $permissionName,
            $description,
            $roleIds,
            $userIds,
            $createdAt,
            $updatedAt
        );
    }

    /**
     * Convenience: restore from a DB row for permissions table.
     *
     * expected keys: id, name, description, created_at, updated_at
     *
     * @param  array<int|string,mixed>  $roleData  Either raw ids or arrays containing an 'id' key.
     * @param  array<int|string,mixed>  $userData  Either raw ids or arrays containing an 'id' key.
     */
    public function restoreFromRow(
        array $row,
        array $roleData = [],
        array $userData = []
    ): Permission {
        $permissionId = new PermissionId((string) $row['id']);
        $permissionName = PermissionName::resolve(['value' => (string) $row['name']]);
        $description = isset($row['description'])
            ? new Description((string) $row['description'])
            : null;
        $createdAt = ! empty($row['created_at'])
            ? new DateTimeImmutable((string) $row['created_at'])
            : null;
        $updatedAt = ! empty($row['updated_at'])
            ? new DateTimeImmutable((string) $row['updated_at'])
            : null;

        $roleIds = [];
        foreach ($roleData as $value) {
            if ($value instanceof RoleId) {
                $roleIds[] = $value;

                continue;
            }

            $raw = is_array($value) ? ($value['id'] ?? null) : $value;
            if ($raw === null) {
                continue;
            }

            $roleIds[] = new RoleId((string) $raw);
        }

        $userIds = [];
        foreach ($userData as $value) {
            if ($value instanceof UserId) {
                $userIds[] = $value;

                continue;
            }

            $raw = is_array($value) ? ($value['id'] ?? null) : $value;
            if ($raw === null) {
                continue;
            }

            $userIds[] = new UserId((string) $raw);
        }

        return $this->restore($permissionId, $permissionName, $description, $roleIds, $userIds, $createdAt, $updatedAt);
    }
}
