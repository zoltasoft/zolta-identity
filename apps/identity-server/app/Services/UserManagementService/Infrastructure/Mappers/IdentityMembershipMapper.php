<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership as DomainIdentityMembership;
use App\Services\UserManagementService\Domain\Enums\IdentityMembershipStatus;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityMembershipId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership as EloquentIdentityMembership;
use DateTimeImmutable;
use Zolta\Domain\ValueObjects\UserId;

final class IdentityMembershipMapper
{
    public static function toDomain(EloquentIdentityMembership $model): DomainIdentityMembership
    {
        return DomainIdentityMembership::reconstitute(
            IdentityMembershipId::fromString((string) $model->id),
            IdentityProjectId::fromString((string) $model->project_id),
            new UserId((string) $model->user_id),
            IdentityMembershipStatus::from((string) $model->status),
            (bool) $model->is_admin,
            (int) $model->authorization_version,
            $model->roles()
                ->pluck('identity_project_roles.id')
                ->map(static fn (mixed $id): IdentityRoleId => IdentityRoleId::fromString((string) $id))
                ->all(),
            $model->permissions()
                ->pluck('identity_project_permissions.id')
                ->map(static fn (mixed $id): IdentityPermissionId => IdentityPermissionId::fromString((string) $id))
                ->all(),
            DateTimeImmutable::createFromInterface($model->created_at),
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    public static function fill(
        EloquentIdentityMembership $model,
        DomainIdentityMembership $membership,
    ): EloquentIdentityMembership {
        return $model->forceFill([
            'id' => $membership->id()->toString(),
            'project_id' => $membership->projectId()->toString(),
            'user_id' => $membership->userId()->toString(),
            'status' => $membership->status()->value,
            'is_admin' => $membership->isAdministrator(),
            'authorization_version' => $membership->authorizationVersion(),
            'created_at' => $membership->createdAt(),
            'updated_at' => $membership->updatedAt(),
        ]);
    }
}
