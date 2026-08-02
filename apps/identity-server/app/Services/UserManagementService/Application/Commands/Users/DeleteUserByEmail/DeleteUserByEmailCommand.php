<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\DeleteUserByEmail;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Email;

final class DeleteUserByEmailCommand extends Command
{
    public function __construct(
        public readonly Email $email,
    ) {}
}
