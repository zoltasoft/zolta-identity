<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Entities;

use DateTimeImmutable;
use Zolta\Domain\Entities\Interfaces\Entity;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\PermissionName;

final class Permission implements \Stringable, Entity
{
    private function __construct(private readonly PermissionId $permissionId, private PermissionName $permissionName, private ?Description $description, private readonly DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt) {}

    public static function restore(
        PermissionId $permissionId,
        PermissionName $permissionName,
        ?Description $description,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self($permissionId, $permissionName, $description, $createdAt, $updatedAt);
    }

    public function equals(self $other): bool
    {
        return $this->permissionId->equals($other->getId());
    }

    public function rename(PermissionName $permissionName): void
    {
        $this->permissionName = $permissionName;
        $this->touch();
    }

    public function changeDescription(?Description $description): void
    {
        $this->description = $description ?? Description::default();
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }

    // ---------- Getters ----------

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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return (string) $this->permissionName->toString();
    }
}
