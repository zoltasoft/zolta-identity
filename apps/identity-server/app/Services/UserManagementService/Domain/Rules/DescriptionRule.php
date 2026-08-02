<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use InvalidArgumentException;

/**
 * Rule to validate description text.
 *
 * Accepts readable text including letters, numbers, standard punctuation,
 * whitespace and common line breaks/tabs. Rejects control / exotic characters.
 */
final readonly class DescriptionRule
{
    private const PATTERN = '/^[\p{L}\p{N}\p{P}\p{Zs}\r\n\t]+$/u';

    public function __construct(
        private string $fieldName = 'description'
    ) {}

    /**
     * Validate the given description value.
     *
     * @throws InvalidArgumentException
     */
    public function validate(string $value): void
    {
        if (! preg_match(self::PATTERN, $value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid %s format. Only readable text, numbers, and standard punctuation are allowed.',
                    $this->fieldName
                )
            );
        }
    }
}
