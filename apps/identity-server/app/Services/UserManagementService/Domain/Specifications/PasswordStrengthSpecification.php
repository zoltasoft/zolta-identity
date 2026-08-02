<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Specifications;

class PasswordStrengthSpecification
{
    public function isSatisfiedBy(string $password): bool
    {
        // Example: Password must be at least 8 characters, contain upper, lower, digit, and special char
        if (strlen($password) < 8) {
            return false;
        }
        if (! preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (! preg_match('/[a-z]/', $password)) {
            return false;
        }
        if (! preg_match('/\d/', $password)) {
            return false;
        }
        if (! preg_match('/[\W]/', $password)) {
            return false;
        }

        return true;
    }
}
