<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use InvalidArgumentException;

final readonly class RegexRule
{
    public function __construct(private string $pattern, private string $name = 'value') {}

    public function validate(string $value): void
    {
        if (! preg_match($this->pattern, $value)) {
            throw new InvalidArgumentException(sprintf('%s does not match expected pattern', $this->name));
        }
    }
}
