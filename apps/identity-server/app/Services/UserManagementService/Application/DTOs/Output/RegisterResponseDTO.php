<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Aggregates\Role;
use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class RegisterResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $user,
    ) {}

    /**
     * Build a DTO or resource from a User domain aggregate.
     */
    public static function fromDomain(User $user, Role $role): self
    {
        return new self([
            'id' => $user->getId()->get('value'),
            'email' => $user->getEmail()->get('address'),
            'email_verified_at' => $user->getEmail()->get('verifiedAt')?->format(DATE_ATOM),
            'username' => $user->getUsername()->get('username'),
            'terms' => $user->getTerms()->value,
            'role' => [
                'id' => $role->getId()->get('value'),
                'name' => $role->getName()->get('value'),
                'permissions' => array_map(
                    static fn (Permission $permission): array => [
                        'id' => $permission->getId()->get('value'),
                        'name' => $permission->getName()->get('value'),
                        'description' => $permission->getDescription()?->get('value'),
                        'roleIds' => array_map(
                            static fn (RoleId $roleId): mixed => $roleId->get('value'),
                            $permission->getRoleIds()
                        ),
                    ],
                    $role->getPermissions()
                ),
            ],
        ]);
    }
}
