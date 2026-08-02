<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use App\Services\UserManagementService\Domain\Enums\IdentityMembershipStatus;
use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityAccessConfigurationException;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityMembershipId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;
use Zolta\Domain\ValueObjects\UserId;

final class IdentityMembership extends AggregateRoot
{
    /**
     * @param  list<IdentityRoleId>  $roleIds
     * @param  list<IdentityPermissionId>  $permissionIds
     */
    private function __construct(
        private readonly IdentityMembershipId $id,
        private readonly IdentityProjectId $projectId,
        private readonly UserId $userId,
        private IdentityMembershipStatus $status,
        private bool $administrator,
        private int $authorizationVersion,
        private array $roleIds,
        private array $permissionIds,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        if ($authorizationVersion < 1) {
            throw new InvalidIdentityAccessConfigurationException(
                'Membership authorization version must be positive.',
            );
        }

        $this->roleIds = $this->uniqueIds($roleIds);
        $this->permissionIds = $this->uniqueIds($permissionIds);
    }

    /** @param list<IdentityRoleId> $roleIds */
    public static function create(
        IdentityProjectId $projectId,
        UserId $userId,
        bool $administrator = false,
        array $roleIds = [],
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            new IdentityMembershipId,
            $projectId,
            $userId,
            IdentityMembershipStatus::Active,
            $administrator,
            1,
            $roleIds,
            [],
            $now,
            $now,
        );
    }

    /**
     * @param  list<IdentityRoleId>  $roleIds
     * @param  list<IdentityPermissionId>  $permissionIds
     */
    public static function reconstitute(
        IdentityMembershipId $id,
        IdentityProjectId $projectId,
        UserId $userId,
        IdentityMembershipStatus $status,
        bool $administrator,
        int $authorizationVersion,
        array $roleIds,
        array $permissionIds,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $projectId,
            $userId,
            $status,
            $administrator,
            $authorizationVersion,
            $roleIds,
            $permissionIds,
            $createdAt,
            $updatedAt,
        );
    }

    /**
     * @param  list<IdentityRoleId>  $roleIds
     * @param  list<IdentityPermissionId>  $permissionIds
     */
    public function updateAccess(
        array $roleIds,
        array $permissionIds,
        bool $administrator,
        IdentityMembershipStatus $status,
    ): void {
        $this->roleIds = $this->uniqueIds($roleIds);
        $this->permissionIds = $this->uniqueIds($permissionIds);
        $this->administrator = $administrator;
        $this->status = $status;
        $this->authorizationVersion++;
        $this->touch();
    }

    public function acceptInvitation(bool $administrator): void
    {
        $changed = $this->status !== IdentityMembershipStatus::Active
            || $this->administrator !== $administrator;
        $this->status = IdentityMembershipStatus::Active;
        $this->administrator = $administrator;

        if ($changed) {
            $this->authorizationVersion++;
            $this->touch();
        }
    }

    public function id(): IdentityMembershipId
    {
        return $this->id;
    }

    public function projectId(): IdentityProjectId
    {
        return $this->projectId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function status(): IdentityMembershipStatus
    {
        return $this->status;
    }

    public function isAdministrator(): bool
    {
        return $this->administrator;
    }

    public function authorizationVersion(): int
    {
        return $this->authorizationVersion;
    }

    /** @return list<IdentityRoleId> */
    public function roleIds(): array
    {
        return $this->roleIds;
    }

    /** @return list<IdentityPermissionId> */
    public function permissionIds(): array
    {
        return $this->permissionIds;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @template T of object @param list<T> $ids @return list<T> */
    private function uniqueIds(array $ids): array
    {
        $unique = [];
        foreach ($ids as $id) {
            $unique[$id->toString()] = $id;
        }

        return array_values($unique);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
