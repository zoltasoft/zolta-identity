<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityPermission as DomainIdentityPermission;
use App\Services\UserManagementService\Domain\Repositories\IdentityPermissionRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityPermissionMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission;

final class EloquentIdentityPermissionRepository implements IdentityPermissionRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityPermissionId $permissionId,
    ): ?DomainIdentityPermission {
        $model = IdentityProjectPermission::query()
            ->where('project_id', $projectId->toString())
            ->find($permissionId->toString());

        return $model ? IdentityPermissionMapper::toDomain($model) : null;
    }

    public function findByKey(
        IdentityProjectId $projectId,
        string $key,
    ): ?DomainIdentityPermission {
        $model = IdentityProjectPermission::query()
            ->where('project_id', $projectId->toString())
            ->where('key', $key)
            ->first();

        return $model ? IdentityPermissionMapper::toDomain($model) : null;
    }

    public function findForManifestClient(
        IdentityProjectId $projectId,
        IdentityClientId $clientId,
    ): array {
        return IdentityProjectPermission::query()
            ->where('project_id', $projectId->toString())
            ->where('source_client_id', $clientId->toString())
            ->get()
            ->map(static fn (IdentityProjectPermission $model): DomainIdentityPermission => IdentityPermissionMapper::toDomain($model))
            ->all();
    }

    public function save(DomainIdentityPermission $permission): void
    {
        $model = IdentityProjectPermission::query()->find($permission->id()->toString())
            ?? new IdentityProjectPermission;

        IdentityPermissionMapper::fill($model, $permission)->save();
    }
}
