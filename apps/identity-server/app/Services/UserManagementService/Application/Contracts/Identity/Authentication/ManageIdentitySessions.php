<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface ManageIdentitySessions
{
    /** @param array<string, mixed> $credentials @return array<string, mixed> */
    public function refresh(array $credentials, ?string $ipAddress = null, ?string $userAgent = null): array;

    public function logout(string $accessToken): void;

    public function revokeSession(string $userId, string $familyId): void;
}
