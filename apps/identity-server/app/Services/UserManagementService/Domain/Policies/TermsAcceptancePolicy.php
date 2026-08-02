<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Policies;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Domain\ValueObjects\Terms;

class TermsAcceptancePolicy
{
    public function canAccessPlatform(User $user): bool
    {
        // Allow only if terms are accepted
        return $user->getTerms() === Terms::accepted;
    }
}
