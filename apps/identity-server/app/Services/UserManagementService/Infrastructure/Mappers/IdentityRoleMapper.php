<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\IdentityRole as DomainIdentityRole;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectRole as EloquentIdentityRole;
use DateTimeImmutable;

final class IdentityRoleMapper
{
    public static function toDomain(EloquentIdentityRole $model): DomainIdentityRole
    {
        return DomainIdentityRole::reconstitute(
            IdentityRoleId::fromString((string) $model->id),
            IdentityProjectId::fromString((string) $model->project_id),
            (string) $model->name,
            (string) $model->slug,
            $model->description !== null ? (string) $model->description : null,
            $model->permissions()
                ->pluck('identity_project_permissions.id')
                ->map(static fn (mixed $id): IdentityPermissionId => IdentityPermissionId::fromString((string) $id))
                ->all(),
            DateTimeImmutable::createFromInterface($model->created_at),
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    public static function fill(
        EloquentIdentityRole $model,
        DomainIdentityRole $role,
    ): EloquentIdentityRole {
        return $model->forceFill([
            'id' => $role->id()->toString(),
            'project_id' => $role->projectId()->toString(),
            'name' => $role->name(),
            'slug' => $role->slug(),
            'description' => $role->description(),
            'created_at' => $role->createdAt(),
            'updated_at' => $role->updatedAt(),
        ]);
    }
}
