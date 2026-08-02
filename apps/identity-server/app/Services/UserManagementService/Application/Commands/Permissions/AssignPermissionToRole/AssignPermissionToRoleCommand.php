<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\AssignPermissionToRole;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\RoleId;

final class AssignPermissionToRoleCommand extends Command
{
    public function __construct(
        public readonly PermissionId $permissionId,
        public readonly RoleId $roleId
    ) {}
}
