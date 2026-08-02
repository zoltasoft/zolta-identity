<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class MinLength
{
    public function __construct(public int $length) {}

    /**
     * Validate value.
     * Return error message string if invalid, null if valid.
     */
    public function validate($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        if (mb_strlen($value) < $this->length) {
            return "Value must be at least {$this->length} characters long.";
        }

        return null;
    }
}
