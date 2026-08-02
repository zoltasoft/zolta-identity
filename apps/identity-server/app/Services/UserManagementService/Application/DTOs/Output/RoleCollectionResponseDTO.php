<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Role;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class RoleCollectionResponseDTO extends ResponseDTO
{
    public function __construct(public readonly array $roles) {}

    /**
     * @param  Role[]  $roles
     */
    public static function fromDomain(array $roles): self
    {
        return new self(array_map(
            static fn (Role $role): array => RoleResponseDTO::fromDomain($role)->role,
            $roles
        ));
    }
}
