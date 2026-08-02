<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class Email
{
    /**
     * Validate value.
     * Return error message string if invalid, null if valid.
     */
    public function validate($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address.';
        }

        return null;
    }
}
