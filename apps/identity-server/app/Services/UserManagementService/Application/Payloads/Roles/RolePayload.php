<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Roles;

use App\Services\UserManagementService\Domain\Aggregates\Role;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class RolePayload implements MessagePayloadInterface
{
    public function __construct(private Role $role) {}

    public function role(): Role
    {
        return $this->role;
    }

    public function toArray(): array
    {
        return ['role' => $this->role];
    }
}
