<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class PermissionResponseDTO extends ResponseDTO
{
    public function __construct(public readonly array $permission) {}

    public static function fromDomain(Permission $permission): self
    {
        return new self([
            'id' => $permission->getId()->get('value'),
            'name' => $permission->getName()->get('value'),
            'description' => $permission->getDescription()?->get('description'),
            'roles' => array_map(
                static fn (RoleId $roleId): mixed => $roleId->get('value'),
                $permission->getRoleIds()
            ),
            'users' => array_map(
                static fn (UserId $userId): mixed => $userId->get('value'),
                $permission->getUserIds()
            ),
            'created_at' => $permission->getCreatedAt()->format('c'),
            'updated_at' => $permission->getUpdatedAt()->format('c'),
        ]);
    }
}
