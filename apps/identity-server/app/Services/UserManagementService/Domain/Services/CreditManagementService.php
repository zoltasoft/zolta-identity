<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Services;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Domain\ValueObjects\Credit;

class CreditManagementService
{
    public function addCredit(User $user, Credit $credit): void
    {
        $current = $user->getCredit();
        $newAmount = $current->get('amount') + $credit->get('amount');
        $user->addCredit(Credit::resolve(['amount' => $newAmount, 'currency' => $current->get('currency')]));

    }

    public function deductCredit(User $user, Credit $credit): void
    {
        $current = $user->getCredit();
        $newAmount = $current->get('amount') - $credit->get('amount');

        if ($newAmount < 0) {
            throw new \InvalidArgumentException('Insufficient credit');
        }

        $user->addCredit(Credit::resolve(['amount' => $newAmount, 'currency' => $current->get('currency')]));

    }

    public function checkCreditBalance(User $user): Credit
    {
        return $user->getCredit();
    }
}
