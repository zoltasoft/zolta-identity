<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Permissions;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

/**
 * @phpstan-type PermissionList array<int, Permission>
 */
final readonly class PermissionCollectionPayload implements MessagePayloadInterface
{
    /**
     * @param  Permission[]  $permissions
     */
    public function __construct(private array $permissions) {}

    /**
     * @return Permission[]
     */
    public function permissions(): array
    {
        return $this->permissions;
    }

    public function toArray(): array
    {
        return ['permissions' => $this->permissions];
    }
}
