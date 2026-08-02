<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership as DomainIdentityMembership;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityMembershipId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityMembershipMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use Illuminate\Support\Facades\DB;
use Zolta\Domain\ValueObjects\UserId;

final class EloquentIdentityMembershipRepository implements IdentityMembershipRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityMembershipId $membershipId,
    ): ?DomainIdentityMembership {
        $model = IdentityProjectMembership::query()
            ->where('project_id', $projectId->toString())
            ->find($membershipId->toString());

        return $model ? IdentityMembershipMapper::toDomain($model) : null;
    }

    public function findForProjectUser(
        IdentityProjectId $projectId,
        UserId $userId,
    ): ?DomainIdentityMembership {
        $model = IdentityProjectMembership::query()
            ->where('project_id', $projectId->toString())
            ->where('user_id', $userId->toString())
            ->first();

        return $model ? IdentityMembershipMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityMembership $membership): void
    {
        DB::transaction(function () use ($membership): void {
            $model = IdentityProjectMembership::query()->find($membership->id()->toString())
                ?? new IdentityProjectMembership;
            IdentityMembershipMapper::fill($model, $membership)->save();
            $model->roles()->sync(
                array_map(
                    static fn ($id): string => $id->toString(),
                    $membership->roleIds(),
                ),
            );
            $model->permissions()->sync(
                array_map(
                    static fn ($id): string => $id->toString(),
                    $membership->permissionIds(),
                ),
            );
        });
    }

    public function delete(DomainIdentityMembership $membership): void
    {
        IdentityProjectMembership::query()
            ->where('project_id', $membership->projectId()->toString())
            ->whereKey($membership->id()->toString())
            ->delete();
    }

    public function incrementAuthorizationVersionForProject(IdentityProjectId $projectId): void
    {
        IdentityProjectMembership::query()
            ->where('project_id', $projectId->toString())
            ->increment('authorization_version');
    }
}
