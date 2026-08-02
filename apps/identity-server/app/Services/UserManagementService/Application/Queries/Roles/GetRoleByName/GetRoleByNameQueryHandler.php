<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Roles\GetRoleByName;

use App\Services\UserManagementService\Application\Payloads\Roles\RolePayload;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GetRoleByNameQuery::class)]
final readonly class GetRoleByNameQueryHandler
{
    public function __construct(private RoleRepository $roleRepository) {}

    public function __invoke(GetRoleByNameQuery $getRoleByNameQuery): Option
    {
        $role = $this->roleRepository->findRoleByName($getRoleByNameQuery->name);

        if ($role === null) {
            return Option::none();
        }

        return Option::some(new RolePayload($role));
    }
}
