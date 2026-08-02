<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Roles\GetRoleById;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\RoleId;

final class GetRoleByIdQuery extends Query
{
    public function __construct(
        public readonly RoleId $id,
        public readonly array $options = []
    ) {}
}
