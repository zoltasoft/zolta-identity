<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Roles\ListRoles;

use Zolta\Cqrs\Queries\Query;

final class ListRolesQuery extends Query
{
    public function __construct(public readonly array $options = []) {}
}
