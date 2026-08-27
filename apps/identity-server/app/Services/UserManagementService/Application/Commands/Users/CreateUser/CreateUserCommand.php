<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\CreateUser;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Terms;
use Zolta\Domain\ValueObjects\Username;

final class CreateUserCommand extends Command
{
    public function __construct(
        public readonly Email $email,
        public readonly string $password,
        public readonly Username $username,
        public readonly Terms $terms,
    ) {}
}
