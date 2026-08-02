<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Aggregates\Role;
use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class GetUserByEmailResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $user,
        public readonly array $captured = [],
    ) {}

    public static function fromDomain(User $user, Role $role, array $captureLog = []): self
    {
        $userPermissions = array_map(
            static fn (Permission $permission): array => [
                'id' => $permission->getId()->get('value'),
                'name' => $permission->getName()->get('value'),
                'description' => $permission->getDescription()?->get('value'),
            ],
            $user->getPermissions()
        );

        $rolePermissions = array_map(
            static fn (Permission $permission): array => [
                'id' => $permission->getId()->get('value'),
                'name' => $permission->getName()->get('value'),
                'description' => $permission->getDescription()?->get('value'),
                'roleIds' => array_map(
                    static fn (RoleId $roleId): array => ['id' => $roleId->get('value')],
                    $permission->getRoleIds()
                ),
            ],
            $role->getPermissions()
        );

        $payload = [
            'id' => $user->getId()->get('value'),
            'email' => $user->getEmail()->get('address'),
            'username' => $user->getUsername()->get('username'),
            'terms' => $user->getTerms()->value,
            'role' => [
                'id' => $role->getId()->get('value'),
                'name' => $role->getName()->get('value'),
                'description' => $role->getDescription()?->get('value'),
                'permissions' => $rolePermissions,
            ],
            'permissions' => $userPermissions,
        ];

        return new self($payload, $captureLog);
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'captured' => $this->captured,
        ];
    }
}
