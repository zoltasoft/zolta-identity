<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface RecoverIdentityPassword
{
    /** @return array<string, mixed> */
    public function requestPasswordReset(string $clientId, string $clientSecret, string $email): array;

    public function resetPassword(
        string $clientId,
        string $clientSecret,
        string $email,
        string $token,
        string $password,
    ): void;
}
