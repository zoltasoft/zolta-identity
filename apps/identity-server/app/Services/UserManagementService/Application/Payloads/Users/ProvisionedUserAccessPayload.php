<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Users;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Application\Payloads\Roles\RolePayload;
use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Aggregates\Role;
use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class ProvisionedUserAccessPayload implements MessagePayloadInterface
{
    /**
     * @param  Permission[]  $permissions
     */
    public function __construct(
        private User $user,
        private Role $role,
        private array $permissions,
    ) {}

    public function user(): User
    {
        return $this->user;
    }

    public function role(): Role
    {
        return $this->role;
    }

    /**
     * @return Permission[]
     */
    public function permissions(): array
    {
        return $this->permissions;
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'role' => $this->role,
            'permissions' => $this->permissions,
            'role_payload' => (new RolePayload($this->role))->toArray(),
            'permissions_payload' => array_map(
                static fn (Permission $permission): array => (new PermissionPayload($permission))->toArray(),
                $this->permissions
            ),
        ];
    }
}
