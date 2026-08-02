<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\CompletePasswordReset;

use Zolta\Cqrs\Commands\Command;

final class CompletePasswordResetCommand extends Command
{
    public function __construct(
        public readonly string $email,
        public readonly string $token,
        public readonly string $password
    ) {}
}
