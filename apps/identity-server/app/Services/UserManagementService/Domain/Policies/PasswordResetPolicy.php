<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Policies;

use App\Services\UserManagementService\Domain\Aggregates\User;
use App\Services\UserManagementService\Domain\Exceptions\AccountLockedException;
use Zolta\Domain\ValueObjects\Terms;

class PasswordResetPolicy
{
    public function canRequestReset(User $user): bool
    {
        // Deny if user account is locked
        if ($user->getTerms() !== Terms::accepted) {
            return false;
        }

        if ($this->isAccountLocked($user)) {
            throw new AccountLockedException;
        }

        // Allow if email is verified
        $email = $user->getEmail();
        if (! $email->isVerified()) {
            return false;
        }

        // Add any other business rules here
        return true;
    }

    public function canCompleteReset(User $user, string $token): bool
    {
        // Check if token is valid and not expired (assuming User has methods for this)
        // For demo, just a placeholder logic

        $storedToken = $user->getPasswordResetToken();
        $expiry = $user->getPasswordResetExpiry();

        if ($storedToken !== $token) {
            return false;
        }

        if ($expiry === null || $expiry < new \DateTime) {
            return false; // expired
        }

        return true;
    }

    private function isAccountLocked(User $user): bool
    {
        // Could integrate with AccountLockoutPolicy if needed
        return false; // stub here
    }
}
