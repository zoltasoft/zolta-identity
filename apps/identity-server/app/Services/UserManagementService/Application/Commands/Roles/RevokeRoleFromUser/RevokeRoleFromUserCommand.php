<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Roles\RevokeRoleFromUser;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\UserId;

final class RevokeRoleFromUserCommand extends Command
{
    public function __construct(
        public readonly RoleId $roleId,
        public readonly UserId $userId,
        public readonly RoleId $fallbackRoleId
    ) {}
}
