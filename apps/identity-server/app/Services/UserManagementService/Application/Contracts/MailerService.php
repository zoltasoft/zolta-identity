<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

use App\Services\UserManagementService\Application\DTOs\External\MailerDTO;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Username;

interface MailerService
{
    public function sendWelcomeUserMessage(MailerDTO $mailerDTO): void;

    public function sendEmailVerificationCode(Email $email, Username $username, string $code): void;
}
