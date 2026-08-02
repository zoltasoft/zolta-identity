<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use InvalidArgumentException;

final class PositiveNumberRule
{
    public function apply(mixed $value, string $param = 'value'): mixed
    {
        if (! is_numeric($value) || $value < 0) {
            throw new InvalidArgumentException("$param must be a non-negative number");
        }

        return $value;
    }
}
