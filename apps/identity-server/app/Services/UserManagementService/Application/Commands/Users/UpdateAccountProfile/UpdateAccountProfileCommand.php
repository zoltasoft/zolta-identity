<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateAccountProfile;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Domain\ValueObjects\Username;

final class UpdateAccountProfileCommand extends Command
{
    public function __construct(
        public readonly UserId $userId,
        public readonly Username $username,
        public readonly Email $email,
        public readonly ?string $profilePicture = null,
    ) {}
}
