<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateUserEmail;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\UserId;

final class UpdateUserEmailCommand extends Command
{
    public function __construct(
        public readonly UserId $id,
        public readonly Email $email,
    ) {}
}
