<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\DeleteRole;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\RoleId;

final class DeleteRoleCommand extends Command
{
    public function __construct(public readonly RoleId $roleId) {}
}
