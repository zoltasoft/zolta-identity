<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Aggregates\Role;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class RoleResponseDTO extends ResponseDTO
{
    public function __construct(public readonly array $role) {}

    public static function fromDomain(Role $role): self
    {
        return new self([
            'id' => $role->getId()->get('value'),
            'name' => $role->getName()->get('value'),
            'description' => $role->getDescription()?->get(),
            'permissions' => array_map(
                static fn (Permission $permission): array => [
                    'id' => $permission->getId()->get('value'),
                    'name' => $permission->getName()->get('value'),
                    'description' => $permission->getDescription()?->get(),
                ],
                $role->getPermissions()
            ),
            'users' => array_map(
                static fn (UserId $userId): mixed => $userId->get('value'),
                $role->getUserIds()
            ),
            'created_at' => $role->getCreatedAt()->format('c'),
            'updated_at' => $role->getUpdatedAt()->format('c'),
        ]);
    }
}
