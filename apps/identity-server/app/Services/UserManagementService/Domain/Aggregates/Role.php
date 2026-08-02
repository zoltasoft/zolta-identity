<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use App\Services\UserManagementService\Domain\Entities\Role as RoleEntity;
use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\RoleName;
use Zolta\Domain\ValueObjects\UserId;

/**
 * Role aggregate root governing role lifecycle and assignments.
 */
final class Role extends AggregateRoot
{
    private readonly DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param  Permission[]  $permissions
     * @param  UserId[]  $userIds
     */
    private function __construct(
        private readonly RoleId $roleId,
        private RoleName $roleName,
        private ?Description $description,
        private array $permissions = [],
        private array $userIds = [],
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable;
    }

    /**
     * Create a new role aggregate.
     *
     * @param  Permission[]  $permissions
     */
    public static function create(
        RoleName $roleName,
        ?Description $description = null,
        array $permissions = []
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            new RoleId,
            $roleName,
            $description,
            $permissions,
            [],
            $now,
            $now
        );

    }

    /**
     * Restore role aggregate from persistence.
     *
     * @param  Permission[]  $permissions
     * @param  UserId[]  $userIds
     */
    public static function restore(
        RoleId $roleId,
        RoleName $roleName,
        ?Description $description = null,
        array $permissions = [],
        array $userIds = [],
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): self {
        return new self(
            $roleId,
            $roleName,
            $description,
            $permissions,
            $userIds,
            $createdAt,
            $updatedAt
        );
    }

    public function rename(RoleName $roleName): void
    {
        if ($this->roleName->equals($roleName)) {
            return;
        }

        $this->roleName = $roleName;
        $this->touch();
    }

    public function changeDescription(?Description $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function assignPermission(Permission $permission): void
    {
        foreach ($this->permissions as $existing) {
            if ($existing->getId()->get('value') === $permission->getId()->get('value')) {
                return;
            }
        }

        $this->permissions[] = $permission;
        $this->touch();
    }

    public function revokePermission(Permission $permission): void
    {
        $removed = false;
        $this->permissions = array_values(array_filter(
            $this->permissions,
            function (Permission $existing) use ($permission, &$removed): bool {
                $same = $existing->getId()->get('value') === $permission->getId()->get('value');
                if ($same) {
                    $removed = true;
                }

                return ! $same;
            }
        ));

        if ($removed) {
            $this->touch();
        }
    }

    /**
     * @param  Permission[]  $permissions
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions = $this->uniquePermissions($permissions);
        $this->touch();
    }

    public function assignToUser(UserId $userId): void
    {
        foreach ($this->userIds as $existing) {
            if ($existing->get('value') === $userId->get('value')) {
                return;
            }
        }

        $this->userIds[] = $userId;
        $this->touch();
    }

    public function revokeFromUser(UserId $userId): void
    {
        $removed = false;
        $this->userIds = array_values(array_filter(
            $this->userIds,
            function (UserId $existing) use ($userId, &$removed): bool {
                $same = $existing->get('value') === $userId->get('value');
                if ($same) {
                    $removed = true;
                }

                return ! $same;
            }
        ));

        if ($removed) {
            $this->touch();
        }
    }

    /**
     * @param  UserId[]  $userIds
     */
    public function syncUsers(array $userIds): void
    {
        $this->userIds = $this->uniqueUserIds($userIds);
        $this->touch();
    }

    public function isSystemRole(): bool
    {
        $systemRoles = ['admin', 'superadmin', 'system'];

        return in_array(
            strtolower((string) $this->roleName->get('value')),
            $systemRoles,
            true
        );
    }

    public function getId(): RoleId
    {
        return $this->roleId;
    }

    public function getName(): RoleName
    {
        return $this->roleName;
    }

    public function getDescription(): ?Description
    {
        return $this->description;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return UserId[]
     */
    public function getUserIds(): array
    {
        return $this->userIds;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Create the lightweight Role entity representation for embedding.
     */
    public function toEntity(): RoleEntity
    {
        return new RoleEntity($this->roleId, $this->roleName, $this->description, $this->permissions);
    }

    /**
     * @param  Permission[]  $permissions
     * @return Permission[]
     */
    private function uniquePermissions(array $permissions): array
    {
        $unique = [];
        foreach ($permissions as $permission) {
            if (! $permission instanceof Permission) {
                continue;
            }
            $unique[$permission->getId()->get('value')] = $permission;
        }

        return array_values($unique);
    }

    /**
     * @param  UserId[]  $userIds
     * @return UserId[]
     */
    private function uniqueUserIds(array $userIds): array
    {
        $unique = [];
        foreach ($userIds as $userId) {
            if (! $userId instanceof UserId) {
                continue;
            }
            $unique[$userId->get('value')] = $userId;
        }

        return array_values($unique);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
