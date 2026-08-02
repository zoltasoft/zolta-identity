<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\SyncIdentityClientManifest;

use Zolta\Cqrs\Commands\Command;

final class SyncIdentityClientManifestCommand extends Command
{
    /** @param list<array{key: string, name?: string, description?: string}> $permissions */
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly array $permissions,
    ) {}
}
