<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Permissions\GetPermissionById;

use App\Services\UserManagementService\Application\Payloads\Permissions\PermissionPayload;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GetPermissionByIdQuery::class)]
final readonly class GetPermissionByIdQueryHandler
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(GetPermissionByIdQuery $getPermissionByIdQuery): Option
    {
        $permission = $this->permissionRepository->findPermissionById($getPermissionByIdQuery->permissionId);

        if ($permission === null) {
            return Option::none();
        }

        return Option::some(new PermissionPayload($permission));
    }
}
