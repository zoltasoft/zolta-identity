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
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryConstraint;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;

final class EloquentIdentityRoleRepository extends BaseRepository implements IdentityRoleRepository
{
    protected array $allowedConstraintFields = ['id', 'project_id'];

    protected bool $enableReadCaching = false;

    protected function modelClass(): string
    {
        return IdentityProjectRole::class;
    }

    public function findForProject(
        IdentityProjectId $projectId,
        IdentityRoleId $roleId,
    ): ?DomainIdentityRole {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $roleId->toString()),
            RepositoryConstraint::equals('project_id', $projectId->toString()),
        ));

        return $model instanceof IdentityProjectRole ? IdentityRoleMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityRole $role): void
    {
        DB::transaction(function () use ($role): void {
            $existing = $this->show($role->id()->toString());
            $model = $existing instanceof IdentityProjectRole ? $existing : new IdentityProjectRole;
            $model = IdentityRoleMapper::fill($model, $role);
            $existing instanceof IdentityProjectRole ? $this->update($model) : $this->create($model);
            $model->permissions()->sync(
                array_map(
                    static fn ($id): string => $id->toString(),
                    $role->permissionIds(),
                ),
            );
        });
    }

    public function remove(DomainIdentityRole $role): void
    {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $role->id()->toString()),
            RepositoryConstraint::equals('project_id', $role->projectId()->toString()),
        ));
        if ($model instanceof IdentityProjectRole) {
            parent::delete($model);
        }
    }
}
