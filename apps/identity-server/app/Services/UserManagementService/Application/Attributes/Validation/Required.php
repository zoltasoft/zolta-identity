<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class Required
{
    /**
     * Validate value.
     * Return error message string if invalid, null if valid.
     */
    public function validate($value): ?string
    {
        if ($value === null || $value === '') {
            return 'This field is required.';
        }

        return null;
    }
}
