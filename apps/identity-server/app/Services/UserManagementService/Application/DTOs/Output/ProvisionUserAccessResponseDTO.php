<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Aggregates\Role;
use App\Services\UserManagementService\Domain\Aggregates\User;
use DateTimeInterface;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class ProvisionUserAccessResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $user,
        public readonly array $role,
        public readonly array $grantedPermissions,
        public readonly array $captured = [],
    ) {}

    /**
     * @param  Permission[]  $permissions
     */
    public static function fromDomain(User $user, Role $role, array $permissions, array $captureLog = []): self
    {
        $userPayload = [
            'id' => $user->getId()->get('value'),
            'email' => $user->getEmail()->get('address'),
            'username' => $user->getUsername()->get('username'),
            'role_id' => $user->getRoleId()->get('value'),
        ];

        $rolePayload = [
            'id' => $role->getId()->get('value'),
            'name' => $role->getName()->get('value'),
            'description' => $role->getDescription()?->get('description'),
            'permissions' => array_map(
                static fn (Permission $permission): array => [
                    'id' => $permission->getId()->get('value'),
                    'name' => $permission->getName()->get('value'),
                ],
                $role->getPermissions()
            ),
        ];

        $grantedPayload = array_map(
            static fn (Permission $permission): array => [
                'id' => $permission->getId()->get('value'),
                'name' => $permission->getName()->get('value'),
                'attached_to_role' => in_array($role->getId()->get('value'), array_map(
                    static fn (RoleId $roleId): mixed => $roleId->get('value'),
                    $permission->getRoleIds()
                ), true),
                'attached_to_user' => in_array($user->getId()->get('value'), array_map(
                    static fn (UserId $userId): mixed => $userId->get('value'),
                    $permission->getUserIds()
                ), true),
                'updated_at' => self::formatDate($permission->getUpdatedAt()),
            ],
            $permissions
        );

        return new self($userPayload, $rolePayload, $grantedPayload, $captureLog);
    }

    private static function formatDate(?DateTimeInterface $dateTime): ?string
    {
        return $dateTime?->format(DATE_ATOM);
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'role' => $this->role,
            'granted_permissions' => $this->grantedPermissions,
            'captured' => $this->captured,
        ];
    }
}
