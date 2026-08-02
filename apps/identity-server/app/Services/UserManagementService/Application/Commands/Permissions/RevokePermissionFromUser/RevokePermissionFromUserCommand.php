<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\RevokePermissionFromUser;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\UserId;

final class RevokePermissionFromUserCommand extends Command
{
    public function __construct(
        public readonly PermissionId $permissionId,
        public readonly UserId $userId
    ) {}
}
