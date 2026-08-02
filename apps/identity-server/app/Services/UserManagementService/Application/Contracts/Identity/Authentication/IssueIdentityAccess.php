<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface IssueIdentityAccess
{
    /** @param array<string, mixed> $credentials @return array<string, mixed> */
    public function login(array $credentials, ?string $ipAddress = null, ?string $userAgent = null): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function register(array $attributes, ?string $ipAddress = null, ?string $userAgent = null): array;

    /** @return array<string, mixed> */
    public function createSandboxSession(
        string $clientId,
        string $clientSecret,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array;
}
