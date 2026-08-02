<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Policies;

use App\Services\UserManagementService\Domain\Aggregates\User;

class AccountLockoutPolicy
{
    public function isLocked(User $user): bool
    {
        return $user->isLocked();
    }

    public function canUnlock(User $user): bool
    {
        $expiry = $user->getLockExpiry();
        if ($expiry !== null && $expiry > new \DateTimeImmutable) {
            return false; // still locked
        }

        // You can add more conditions here (e.g., admin override)
        return true;
    }
}
