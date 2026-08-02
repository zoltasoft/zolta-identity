<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Roles\GetRoleByName;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\RoleName;

final class GetRoleByNameQuery extends Query
{
    public function __construct(
        public readonly RoleName $name
    ) {}
}
