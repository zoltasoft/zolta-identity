<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Identity\ReadIdentityInstallation;

use Zolta\Cqrs\Queries\Query;

final class ReadIdentityInstallationQuery extends Query
{
    public function __construct(
        public readonly string $operation,
        public readonly string $actorUserId,
    ) {}
}
