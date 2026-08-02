<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\CreatePermission;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\PermissionName;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

final class CreatePermissionCommand extends Command
{
    /**
     * @param  RoleId[]  $roleIds
     * @param  UserId[]  $userIds
     */
    public function __construct(
        public readonly PermissionName $name,
        public readonly ?Description $description = null,
        public readonly array $roleIds = [],
        public readonly array $userIds = []
    ) {}
}
