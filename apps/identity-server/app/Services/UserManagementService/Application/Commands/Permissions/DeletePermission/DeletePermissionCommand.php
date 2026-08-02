<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\DeletePermission;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\PermissionId;

final class DeletePermissionCommand extends Command
{
    public function __construct(public readonly PermissionId $permissionId) {}
}
