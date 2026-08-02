<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Identity\ReadIdentityAccess;

use Zolta\Cqrs\Queries\Query;

final class ReadIdentityAccessQuery extends Query
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly ?string $project = null,
    ) {}
}
