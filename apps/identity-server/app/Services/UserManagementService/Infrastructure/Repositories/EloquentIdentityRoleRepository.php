<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityRole as DomainIdentityRole;
use App\Services\UserManagementService\Domain\Repositories\IdentityRoleRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityRoleMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectRole;
use Illuminate\Support\Facades\DB;

final class EloquentIdentityRoleRepository implements IdentityRoleRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityRoleId $roleId,
    ): ?DomainIdentityRole {
        $model = IdentityProjectRole::query()
            ->where('project_id', $projectId->toString())
            ->find($roleId->toString());

        return $model ? IdentityRoleMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityRole $role): void
    {
        DB::transaction(function () use ($role): void {
            $model = IdentityProjectRole::query()->find($role->id()->toString())
                ?? new IdentityProjectRole;
            IdentityRoleMapper::fill($model, $role)->save();
            $model->permissions()->sync(
                array_map(
                    static fn ($id): string => $id->toString(),
                    $role->permissionIds(),
                ),
            );
        });
    }
}
