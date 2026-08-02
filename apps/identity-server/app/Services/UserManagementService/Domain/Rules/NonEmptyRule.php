<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use Zolta\Domain\Contracts\Rule;

final class NonEmptyRule extends Rule
{
    public function validate(mixed $value, array $options = []): void
    {
        $paramName = $options['paramName'] ?? 'value';
        if ($value === null || (is_string($value) && trim($value) === '')) {
            throw new \InvalidArgumentException(sprintf('%s cannot be empty', $paramName));
        }
    }
}
