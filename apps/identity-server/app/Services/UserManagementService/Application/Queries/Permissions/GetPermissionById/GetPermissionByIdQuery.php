<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\PermissionId;

final class GetPermissionByIdQuery extends Query
{
    public function __construct(public readonly PermissionId $permissionId) {}
}
