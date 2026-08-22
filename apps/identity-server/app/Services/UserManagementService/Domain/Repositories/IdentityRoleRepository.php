<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityRole;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;

interface IdentityRoleRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityRoleId $roleId,
    ): ?IdentityRole;

    public function save(IdentityRole $role): void;

    public function remove(IdentityRole $role): void;
}
