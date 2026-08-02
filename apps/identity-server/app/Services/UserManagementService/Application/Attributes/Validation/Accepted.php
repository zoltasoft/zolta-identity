<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Attributes\Validation;

use Attribute;
use Zolta\Support\Casts\BooleanParser;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class Accepted
{
    public function validate($value): ?string
    {
        // If the mapper already parsed to bool, accept only true.
        if (is_bool($value)) {
            return $value === true ? null : 'You must accept to continue.';
        }

        // If value is raw, try parsing it robustly
        $parsed = BooleanParser::parse($value);

        if ($parsed === true) {
            return null;
        }

        // parsed === false or null -> invalid for "accepted"
        return 'You must accept to continue.';
    }
}
