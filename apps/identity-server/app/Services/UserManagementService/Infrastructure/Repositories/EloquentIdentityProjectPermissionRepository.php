<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission;

/** @extends EloquentProjectScopedRepository<IdentityProjectPermission> */
final class EloquentIdentityProjectPermissionRepository extends EloquentProjectScopedRepository
{
    protected array $allowedFilters = ['key'];

    protected function modelClass(): string
    {
        return IdentityProjectPermission::class;
    }
}
