<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface VerifyIdentityEmail
{
    /** @return array<string, mixed> */
    public function resendEmailVerification(string $userId): array;

    public function verifyEmail(string $userId, string $code): void;
}
