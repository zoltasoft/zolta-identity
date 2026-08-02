<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Authentication\ExportAccountData;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\UserId;

final class ExportAccountDataQuery extends Query
{
    public function __construct(public readonly UserId $userId) {}
}
