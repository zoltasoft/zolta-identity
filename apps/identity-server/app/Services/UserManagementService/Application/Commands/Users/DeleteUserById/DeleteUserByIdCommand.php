<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\DeleteUserById;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\UserId;

final class DeleteUserByIdCommand extends Command
{
    public function __construct(
        public readonly UserId $id,
    ) {}
}
