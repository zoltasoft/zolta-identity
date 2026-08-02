<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use Zolta\Domain\Contracts\Rule;

final class MaxLengthRule extends Rule
{
    public function validate(mixed $value, array $options = []): void
    {
        $max = $options['max'] ?? null;
        $paramName = $options['paramName'] ?? 'value';

        if ($max === null) {
            throw new \InvalidArgumentException("MaxLengthRule requires option 'max'");
        }
        if (mb_strlen((string) $value) > (int) $max) {
            throw new \InvalidArgumentException(sprintf('%s cannot exceed %d characters', $paramName, $max));
        }
    }
}
