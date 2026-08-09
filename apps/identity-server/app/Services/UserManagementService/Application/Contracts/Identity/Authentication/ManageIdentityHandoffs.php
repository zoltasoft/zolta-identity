<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface ManageIdentityHandoffs
{
    /** @param array<string, mixed> $input @return array<string, mixed> */
    public function createHandoff(
        string $userId,
        string $accessToken,
        array $input,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array;

    /** @param array<string, mixed> $input @return array<string, mixed> */
    public function exchangeHandoff(
        array $input,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array;
}
