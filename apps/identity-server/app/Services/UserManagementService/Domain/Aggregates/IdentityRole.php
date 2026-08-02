<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityAccessConfigurationException;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;

final class IdentityRole extends AggregateRoot
{
    /** @param list<IdentityPermissionId> $permissionIds */
    private function __construct(
        private readonly IdentityRoleId $id,
        private readonly IdentityProjectId $projectId,
        private string $name,
        private string $slug,
        private ?string $description,
        private array $permissionIds,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->assertIdentity($name, $slug);
        $this->permissionIds = $this->uniqueIds($permissionIds);
    }

    public static function create(
        IdentityProjectId $projectId,
        string $name,
        string $slug,
        ?string $description = null,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            new IdentityRoleId,
            $projectId,
            $name,
            $slug,
            $description,
            [],
            $now,
            $now,
        );
    }

    /** @param list<IdentityPermissionId> $permissionIds */
    public static function reconstitute(
        IdentityRoleId $id,
        IdentityProjectId $projectId,
        string $name,
        string $slug,
        ?string $description,
        array $permissionIds,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $projectId,
            $name,
            $slug,
            $description,
            $permissionIds,
            $createdAt,
            $updatedAt,
        );
    }

    /** @param list<IdentityPermissionId> $permissionIds */
    public function assignPermissions(array $permissionIds): void
    {
        $this->permissionIds = $this->uniqueIds($permissionIds);
        $this->touch();
    }

    public function id(): IdentityRoleId
    {
        return $this->id;
    }

    public function projectId(): IdentityProjectId
    {
        return $this->projectId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
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

    private function assertIdentity(string $name, string $slug): void
    {
        if (trim($name) === '' || trim($slug) === '') {
            throw new InvalidIdentityAccessConfigurationException(
                'Role name and slug cannot be empty.',
            );
        }
    }

    /**
     * @param  list<IdentityPermissionId>  $ids
     * @return list<IdentityPermissionId>
     */
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
