<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Roles;

use App\Services\UserManagementService\Domain\Aggregates\Role;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

/**
 * @internal Represents a collection of roles for CQRS responses.
 *
 * @phpstan-type RoleList array<int, Role>
 */
final readonly class RoleCollectionPayload implements MessagePayloadInterface
{
    /**
     * @param  Role[]  $roles
     */
    public function __construct(private array $roles) {}

    /**
     * @return Role[]
     */
    public function roles(): array
    {
        return $this->roles;
    }

    public function toArray(): array
    {
        return ['roles' => $this->roles];
    }
}
