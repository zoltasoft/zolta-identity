<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Specifications;

class ValidUsernameSpecification
{
    public function isSatisfiedBy(string $username): bool
    {
        // Example: username must be 3-20 chars, alphanumeric plus underscores
        if (strlen($username) < 3 || strlen($username) > 20) {
            return false;
        }
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return false;
        }

        return true;
    }
}
