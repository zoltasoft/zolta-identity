<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\CreateRole;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Description;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\RoleName;

final class CreateRoleCommand extends Command
{
    /**
     * @param  PermissionId[]  $permissionIds
     */
    public function __construct(
        public readonly RoleName $name,
        public readonly ?Description $description = null,
        public readonly array $permissionIds = []
    ) {}
}
