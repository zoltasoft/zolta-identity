<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Permissions\ListPermissions;

use Zolta\Cqrs\Queries\Query;

final class ListPermissionsQuery extends Query
{
    public function __construct(public readonly array $options = []) {}
}
