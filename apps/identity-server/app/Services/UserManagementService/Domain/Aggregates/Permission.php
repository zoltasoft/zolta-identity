<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\PermissionName;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

/**
 * Permission aggregate root responsible for its lifecycle and assignments.
 */
final class Permission extends AggregateRoot
{
    private readonly DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    private function __construct(
        private readonly PermissionId $permissionId,
        private PermissionName $permissionName,
        private ?Description $description,
        /** @var RoleId[] */
        private array $roleIds = [],
        /** @var UserId[] */
        private array $userIds = [],
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable;
    }

    public static function create(
        PermissionName $permissionName,
        ?Description $description = null
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            new PermissionId,
            $permissionName,
            $description,
            [],
            [],
            $now,
            $now
        );

    }

    public static function restore(
        PermissionId $permissionId,
        PermissionName $permissionName,
        ?Description $description = null,
        array $roleIds = [],
        array $userIds = [],
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): self {
        return new self(
            $permissionId,
            $permissionName,
            $description,
            $roleIds,
            $userIds,
            $createdAt,
            $updatedAt
        );
    }

    public function rename(PermissionName $permissionName): void
    {
        if ($this->permissionName->equals($permissionName)) {
            return;
        }

        $this->permissionName = $permissionName;
        $this->touch();
    }

    public function changeDescription(?Description $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function assignToRole(RoleId $roleId): void
    {
        foreach ($this->roleIds as $assigned) {
            if ($assigned->get('value') === $roleId->get('value')) {
                return;
            }
        }

        $this->roleIds[] = $roleId;
        $this->touch();
    }

    public function revokeFromRole(RoleId $roleId): void
    {
        $removed = false;
        $this->roleIds = array_values(array_filter(
            $this->roleIds,
            function (RoleId $existing) use ($roleId, &$removed): bool {
                $same = $existing->get('value') === $roleId->get('value');
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

    public function assignToUser(UserId $userId): void
    {
        foreach ($this->userIds as $assigned) {
            if ($assigned->get('value') === $userId->get('value')) {
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

    public function syncRoles(array $roleIds): void
    {
        $this->roleIds = $this->uniqueRoleIds($roleIds);
        $this->touch();
    }

    public function syncUsers(array $userIds): void
    {
        $this->userIds = $this->uniqueUserIds($userIds);
        $this->touch();
    }

    public function getId(): PermissionId
    {
        return $this->permissionId;
    }

    public function getName(): PermissionName
    {
        return $this->permissionName;
    }

    public function getDescription(): ?Description
    {
        return $this->description;
    }

    /**
     * @return RoleId[]
     */
    public function getRoleIds(): array
    {
        return $this->roleIds;
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

    private function uniqueRoleIds(array $roleIds): array
    {
        $unique = [];
        foreach ($roleIds as $roleId) {
            if (! $roleId instanceof RoleId) {
                continue;
            }
            $unique[$roleId->get('value')] = $roleId;
        }

        return array_values($unique);
    }

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
