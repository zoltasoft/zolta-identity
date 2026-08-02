<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\IdentityPermission as DomainIdentityPermission;
use App\Services\UserManagementService\Domain\Enums\IdentityPermissionSource;
use App\Services\UserManagementService\Domain\Enums\IdentityPermissionStatus;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission as EloquentIdentityPermission;
use DateTimeImmutable;

final class IdentityPermissionMapper
{
    public static function toDomain(EloquentIdentityPermission $model): DomainIdentityPermission
    {
        return DomainIdentityPermission::reconstitute(
            IdentityPermissionId::fromString((string) $model->id),
            IdentityProjectId::fromString((string) $model->project_id),
            $model->source_client_id !== null
                ? IdentityClientId::fromString((string) $model->source_client_id)
                : null,
            (string) $model->key,
            (string) $model->name,
            $model->description !== null ? (string) $model->description : null,
            IdentityPermissionSource::from((string) $model->source),
            IdentityPermissionStatus::from((string) $model->status),
            DateTimeImmutable::createFromInterface($model->created_at),
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    public static function fill(
        EloquentIdentityPermission $model,
        DomainIdentityPermission $permission,
    ): EloquentIdentityPermission {
        return $model->forceFill([
            'id' => $permission->id()->toString(),
            'project_id' => $permission->projectId()->toString(),
            'source_client_id' => $permission->sourceClientId()?->toString(),
            'key' => $permission->key(),
            'name' => $permission->name(),
            'description' => $permission->description(),
            'source' => $permission->source()->value,
            'status' => $permission->status()->value,
            'created_at' => $permission->createdAt(),
            'updated_at' => $permission->updatedAt(),
        ]);
    }
}
