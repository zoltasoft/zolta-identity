<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\External;

use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Username;

final readonly class MailerDTO
{
    public function __construct(
        public string $companyName,
        public string $subject,
        public Email $email,
        public Username $username,
        public ?string $verificationCode = null,
    ) {}
}
