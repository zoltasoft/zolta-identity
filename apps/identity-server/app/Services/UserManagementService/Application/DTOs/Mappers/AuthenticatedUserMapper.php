<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\User;

class AuthenticatedUserMapper
{
    /**
     * Maps an Eloquent model to a domain aggregate by reconstituting it.
     */
    public static function toArray(User $user): array
    {
        return [
            'id' => (string) $user->getId(),
            'email' => $user->getEmail()->get('address'),
            'email_verified_at' => $user->getEmail()->get('verifiedAt')?->format('Y-m-d H:i:s'),
            'username' => (string) $user->getUsername()->get('username'),
            'credit' => $user->getCredit()->get()['amount'],
            'terms' => $user->getTerms()->value,
        ];
    }
}
