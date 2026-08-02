<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\UpdatePermission;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\PermissionName;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

final class UpdatePermissionCommand extends Command
{
    /**
     * @param  RoleId[]|null  $roleIds
     * @param  UserId[]|null  $userIds
     */
    public function __construct(
        public readonly PermissionId $permissionId,
        public readonly ?PermissionName $name = null,
        public readonly ?Description $description = null,
        public readonly ?array $roleIds = null,
        public readonly ?array $userIds = null
    ) {}
}
