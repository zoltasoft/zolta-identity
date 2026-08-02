<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Specifications;

class UniqueEmailSpecification
{
    public function isSatisfiedBy(string $email): bool
    {
        // Placeholder example: check email uniqueness
        // In real case, check DB or repository for existing email
        // Here we always return true for example purposes
        return true;
    }
}
