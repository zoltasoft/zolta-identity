<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityAuditEvent;

/** @extends EloquentProjectScopedRepository<IdentityAuditEvent> */
final class EloquentIdentityAuditEventRepository extends EloquentProjectScopedRepository
{
    protected array $allowedFilters = ['created_at'];

    protected function modelClass(): string
    {
        return IdentityAuditEvent::class;
    }
}
