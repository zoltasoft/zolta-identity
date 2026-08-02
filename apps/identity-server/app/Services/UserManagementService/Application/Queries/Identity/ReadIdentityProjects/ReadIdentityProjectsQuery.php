<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Identity\ReadIdentityProjects;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityProjectReadOperation;
use Zolta\Cqrs\Queries\Query;

final class ReadIdentityProjectsQuery extends Query
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityProjectReadOperation $operation,
        public readonly array $input,
        public readonly string $actorUserId,
    ) {}
}
