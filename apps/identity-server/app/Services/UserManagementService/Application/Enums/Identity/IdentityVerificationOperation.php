<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityVerificationOperation: string
{
    case Resend = 'auth.verification.resend';
    case Verify = 'auth.verification.verify';
}
