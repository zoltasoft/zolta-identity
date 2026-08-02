<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SendWelcomeMessage;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\UserId;

final class SendWelcomeMessageCommand extends Command
{
    public function __construct(
        public readonly UserId $id,
    ) {}
}
