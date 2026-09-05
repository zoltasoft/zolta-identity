<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface VerifyIdentityEmail
{
    /** @return array<string, mixed> */
    public function resendEmailVerification(string $userId, string $accessToken): array;

    public function verifyEmail(string $userId, string $accessToken, string $code): void;
}
