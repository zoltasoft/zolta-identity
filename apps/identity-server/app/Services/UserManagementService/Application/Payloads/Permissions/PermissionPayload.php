<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Permissions;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class PermissionPayload implements MessagePayloadInterface
{
    public function __construct(private Permission $permission) {}

    public function permission(): Permission
    {
        return $this->permission;
    }

    public function toArray(): array
    {
        return ['permission' => $this->permission];
    }
}
