<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Authentication;

interface SyncIdentityClientManifest
{
    /** @param list<array{key: string, name?: string, description?: string}> $manifest @return list<array<string, mixed>> */
    public function syncOwnPermissionManifest(string $clientId, string $clientSecret, array $manifest): array;
}
