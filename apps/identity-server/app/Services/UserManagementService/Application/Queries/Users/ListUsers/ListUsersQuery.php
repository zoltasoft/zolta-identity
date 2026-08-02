<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Users\ListUsers;

use Zolta\Cqrs\Queries\Query;

final class ListUsersQuery extends Query
{
    /**
     * @param  array<string,mixed>  $options
     */
    public function __construct(public readonly array $options = []) {}
}
