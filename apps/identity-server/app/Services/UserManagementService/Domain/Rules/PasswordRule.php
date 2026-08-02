<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Rules;

use Zolta\Domain\Contracts\Rule;

final class PasswordRule extends Rule
{
    public function __construct(private readonly int $minLength = 8, private readonly bool $requireSpecial = false, private readonly bool $requireNumber = true) {}

    /**
     * Validate the password. Throws if invalid.
     *
     * @throws \InvalidArgumentException
     */
    public function __invoke(mixed $value): void
    {
        if (! is_string($value)) {
            throw new \InvalidArgumentException('Password must be a string.');
        }

        $password = $value;

        if (mb_strlen($password) < $this->minLength) {
            throw new \InvalidArgumentException("Password must be at least {$this->minLength} characters long.");
        }

        if ($this->requireNumber && ! preg_match('/\d/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one number.');
        }

        if ($this->requireSpecial && ! preg_match('/[\W_]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one special character.');
        }
    }
}
