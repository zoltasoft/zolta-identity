<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Invariants;

use DomainException;

final class CreditInvariant
{
    public function ensure(mixed $value): void
    {
        if (! is_array($value) || ! isset($value['amount'], $value['currency'])) {
            throw new DomainException('Invalid credit structure.');
        }
        if (! is_numeric($value['amount']) || $value['amount'] < 0) {
            throw new DomainException('Credit amount must be non-negative.');
        }
        if (! is_string($value['currency']) || $value['currency'] === '') {
            throw new DomainException('Currency must be provided.');
        }
    }
}
