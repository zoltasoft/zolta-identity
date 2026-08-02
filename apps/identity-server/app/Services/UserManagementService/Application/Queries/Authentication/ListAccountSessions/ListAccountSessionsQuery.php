<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Authentication\ListAccountSessions;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\UserId;

final class ListAccountSessionsQuery extends Query
{
    public function __construct(
        public readonly UserId $userId,
        public readonly ?int $currentTokenId = null,
    ) {}
}
