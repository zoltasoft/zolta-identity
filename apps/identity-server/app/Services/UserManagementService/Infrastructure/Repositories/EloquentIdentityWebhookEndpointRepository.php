<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint;

/** @extends EloquentProjectScopedRepository<IdentityWebhookEndpoint> */
final class EloquentIdentityWebhookEndpointRepository extends EloquentProjectScopedRepository
{
    protected array $allowedFilters = ['url'];

    protected function modelClass(): string
    {
        return IdentityWebhookEndpoint::class;
    }
}
