<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Authentication\GenerateTokenFromUser;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\UserId;

class GenerateTokenFromUserQuery extends Query
{
    public function __construct(
        public readonly UserId $id,
    ) {}
}
