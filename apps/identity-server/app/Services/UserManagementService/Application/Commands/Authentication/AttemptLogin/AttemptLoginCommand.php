<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\AttemptLogin;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\UserCredential;

final class AttemptLoginCommand extends Command
{
    public function __construct(
        public readonly UserCredential $credential
    ) {}
}
