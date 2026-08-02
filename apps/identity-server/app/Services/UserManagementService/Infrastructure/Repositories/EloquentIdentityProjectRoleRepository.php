<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectRole;

/** @extends EloquentProjectScopedRepository<IdentityProjectRole> */
final class EloquentIdentityProjectRoleRepository extends EloquentProjectScopedRepository
{
    protected array $allowedFilters = ['name'];

    protected array $allowedRelations = ['permissions'];

    protected function modelClass(): string
    {
        return IdentityProjectRole::class;
    }
}
