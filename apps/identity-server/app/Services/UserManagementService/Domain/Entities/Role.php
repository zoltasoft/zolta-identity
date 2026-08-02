<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Entities;

use Zolta\Domain\Entities\Interfaces\Entity;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\RoleName;

final readonly class Role implements Entity
{
    private Description $description;

    public function __construct(private RoleId $roleId, private RoleName $roleName, ?Description $description = null, private array $permissions = [])
    {
        $this->description = $description ?? new Description('');
    }

    public function equals(self $other): bool
    {
        return $this->roleId->equals($other->getId());
    }

    public function isSystemRole(): bool
    {
        $systemRoles = ['admin', 'superadmin', 'system'];

        return in_array(strtolower((string) $this->roleName), $systemRoles, true);
    }

    // ---------- Getters ----------

    public function getId(): RoleId
    {
        return $this->roleId;
    }

    public function getName(): RoleName
    {
        return $this->roleName;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
