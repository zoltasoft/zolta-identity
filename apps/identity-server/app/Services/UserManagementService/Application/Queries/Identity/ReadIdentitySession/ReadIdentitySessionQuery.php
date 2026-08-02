<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Identity\ReadIdentitySession;

use App\Services\UserManagementService\Application\Enums\Identity\IdentitySessionReadOperation;
use Zolta\Cqrs\Queries\Query;

final class ReadIdentitySessionQuery extends Query
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentitySessionReadOperation $operation,
        public readonly array $input,
        public readonly ?string $actorUserId = null,
        public readonly ?string $accessToken = null,
    ) {}
}
