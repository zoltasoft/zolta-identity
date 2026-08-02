<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface ReadIdentityAccessContext
{
    /** @return array<string, mixed> */
    public function authenticationContext(
        string $clientId,
        string $clientSecret,
        ?string $project = null,
    ): array;
}
