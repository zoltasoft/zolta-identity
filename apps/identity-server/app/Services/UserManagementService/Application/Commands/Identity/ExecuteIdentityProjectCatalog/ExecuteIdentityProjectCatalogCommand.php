<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityProjectCatalog;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityProjectCatalogOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityProjectCatalogCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityProjectCatalogOperation $operation,
        public readonly array $input,
        public readonly string $actorUserId,
    ) {}
}
