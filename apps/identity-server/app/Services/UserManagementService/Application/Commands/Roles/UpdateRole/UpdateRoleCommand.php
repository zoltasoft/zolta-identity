<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\UpdateRole;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\RoleName;

final class UpdateRoleCommand extends Command
{
    /**
     * @param  PermissionId[]|null  $permissionIds
     */
    public function __construct(
        public readonly RoleId $roleId,
        public readonly ?RoleName $name = null,
        public readonly ?Description $description = null,
        public readonly ?array $permissionIds = null
    ) {}
}
