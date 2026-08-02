<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use InvalidArgumentException;

/**
 * Rule to ensure a permission name follows a consistent naming convention.
 *
 * Examples of valid permission names:
 *  - users.read
 *  - manage_users
 *  - roles.assign
 */
final readonly class PermissionNameRule
{
    private const PATTERN = '/^[a-z][a-z0-9_\.]{2,99}$/'; // starts with a-z, then letters/numbers/underscores/dots

    /**
     * @param  string  $fieldName  Used for exception message context
     */
    public function __construct(
        private string $fieldName = 'permission name'
    ) {}

    /**
     * Validate the given value.
     *
     * @throws InvalidArgumentException
     */
    public function validate(string $value): void
    {
        if (! preg_match(self::PATTERN, $value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid %s format. Must start with a lowercase letter and contain only lowercase letters, numbers, underscores, or dots.',
                    $this->fieldName
                )
            );
        }
    }
}
