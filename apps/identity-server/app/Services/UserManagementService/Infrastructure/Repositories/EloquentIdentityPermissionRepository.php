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
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryConstraint;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;

final class EloquentIdentityPermissionRepository extends BaseRepository implements IdentityPermissionRepository
{
    protected array $allowedConstraintFields = ['id', 'project_id', 'key', 'source_client_id'];

    protected bool $enableReadCaching = false;

    protected function modelClass(): string
    {
        return IdentityProjectPermission::class;
    }

    public function findForProject(
        IdentityProjectId $projectId,
        IdentityPermissionId $permissionId,
    ): ?DomainIdentityPermission {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $permissionId->toString()),
            RepositoryConstraint::equals('project_id', $projectId->toString()),
        ));

        return $model instanceof IdentityProjectPermission ? IdentityPermissionMapper::toDomain($model) : null;
    }

    public function findByKey(
        IdentityProjectId $projectId,
        string $key,
    ): ?DomainIdentityPermission {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('project_id', $projectId->toString()),
            RepositoryConstraint::equals('key', $key),
        ));

        return $model instanceof IdentityProjectPermission ? IdentityPermissionMapper::toDomain($model) : null;
    }

    public function findForManifestClient(
        IdentityProjectId $projectId,
        IdentityClientId $clientId,
    ): array {
        $models = $this->all(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('project_id', $projectId->toString()),
            RepositoryConstraint::equals('source_client_id', $clientId->toString()),
        ));

        return collect($models)
            ->map(static fn (IdentityProjectPermission $model): DomainIdentityPermission => IdentityPermissionMapper::toDomain($model))
            ->all();
    }

    public function save(DomainIdentityPermission $permission): void
    {
        $existing = $this->show($permission->id()->toString());
        $model = $existing instanceof IdentityProjectPermission ? $existing : new IdentityProjectPermission;

        $model = IdentityPermissionMapper::fill($model, $permission);
        $existing instanceof IdentityProjectPermission ? $this->update($model) : $this->create($model);
    }

    public function remove(DomainIdentityPermission $permission): void
    {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $permission->id()->toString()),
            RepositoryConstraint::equals('project_id', $permission->projectId()->toString()),
        ));
        if ($model instanceof IdentityProjectPermission) {
            parent::delete($model);
        }
    }
}
