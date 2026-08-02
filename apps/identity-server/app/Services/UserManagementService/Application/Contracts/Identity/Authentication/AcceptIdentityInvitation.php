<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface AcceptIdentityInvitation
{
    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function acceptInvitation(array $attributes): array;
}
