<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface ReadIdentitySessions
{
    /** @return array<string, mixed> */
    public function introspect(string $clientId, string $clientSecret, string $accessToken): array;

    /** @return array<string, mixed> */
    public function currentIdentity(string $userId, string $accessToken): array;

    /** @return list<array<string, mixed>> */
    public function listSessions(string $userId, string $accessToken): array;
}
