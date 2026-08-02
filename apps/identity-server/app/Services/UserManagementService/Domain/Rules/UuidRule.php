<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use DomainException;
use Zolta\Domain\Contracts\Rule;

final class UuidRule extends Rule
{
    public function validate(mixed $value, array $options = []): void
    {
        if (! is_string($value) || ! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            throw new DomainException("Value '{$value}' is not a valid UUID.");
        }
    }
}
