<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\ProvisionUserAccess;

use Zolta\Cqrs\Commands\Command;

final class ProvisionUserAccessCommand extends Command
{
    /**
     * @param  array<int,string>  $permissionIds
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $roleId,
        public readonly array $permissionIds = [],
        public readonly bool $attachPermissionsToRole = true,
    ) {}
}
