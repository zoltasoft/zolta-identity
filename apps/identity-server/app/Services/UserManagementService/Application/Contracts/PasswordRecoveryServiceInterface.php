<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

interface PasswordRecoveryServiceInterface
{
    public function requestResetLink(string $email): void;

    public function resetPassword(string $email, string $token, string $password): string;
}
