<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class PermissionCollectionResponseDTO extends ResponseDTO
{
    public function __construct(public readonly array $permissions) {}

    /**
     * @param  Permission[]  $permissions
     */
    public static function fromDomain(array $permissions): self
    {
        return new self(array_map(
            static fn (Permission $permission): array => PermissionResponseDTO::fromDomain($permission)->permission,
            $permissions
        ));
    }
}
