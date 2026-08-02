<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\MailerService;
use App\Services\UserManagementService\Application\DTOs\External\MailerDTO;
use App\Services\UserManagementService\Infrastructure\Mail\Mailable\WelcomeUser;
use App\Services\UserManagementService\Infrastructure\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Username;

class LaravelMailerService implements MailerService
{
    public function sendWelcomeUserMessage(MailerDTO $dto): void
    {
        Mail::to($dto->email->get('address'))->queue(
            new WelcomeUser(
                $dto->companyName,
                $dto->subject,
                $dto->username->get('username'),
                $dto->verificationCode
            )
        );
    }

    public function sendEmailVerificationCode(Email $email, Username $username, string $code): void
    {
        Mail::to($email->get('address'))->queue(
            new VerificationEmail($code, $username->get('username'))
        );
    }
}
