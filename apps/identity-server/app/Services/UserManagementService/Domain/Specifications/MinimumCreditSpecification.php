<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Specifications;

use Zolta\Domain\ValueObjects\Credit;

class MinimumCreditSpecification
{
    public function __construct(private readonly float $minimum) {}

    public function isSatisfiedBy(Credit $credit): bool
    {
        // Check if credit amount meets minimum
        return $credit->get('amount') >= $this->minimum;
    }
}
