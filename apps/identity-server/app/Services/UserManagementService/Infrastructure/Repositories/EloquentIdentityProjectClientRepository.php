<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;

/** @extends EloquentProjectScopedRepository<IdentityProjectClient> */
final class EloquentIdentityProjectClientRepository extends EloquentProjectScopedRepository
{
    protected array $allowedFilters = ['name'];

    protected function modelClass(): string
    {
        return IdentityProjectClient::class;
    }
}
