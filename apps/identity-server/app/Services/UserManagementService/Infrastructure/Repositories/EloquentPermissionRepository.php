<?php

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\Permission as DomainPermission;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use App\Services\UserManagementService\Infrastructure\Mappers\PermissionMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Permission as EloquentPermission;
use Zolta\Cqrs\Laravel\Eloquent\EloquentBaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;
use Zolta\Domain\Repositories\Query\AbstractQueryOptions;
use Zolta\Domain\ValueObjects\PermissionId;
use Zolta\Domain\ValueObjects\PermissionName;

class EloquentPermissionRepository extends EloquentBaseRepository implements PermissionRepository
{
    protected array $allowedFilters = [
        'id',
        'name',
    ];

    protected function modelClass(): string
    {
        return EloquentPermission::class;
    }

    protected function getAllowedRelations(): array
    {
        return ['roles', 'users'];
    }

    public function getAllPermissions(AbstractQueryOptions $options): iterable
    {
        $query = $this->query($options);
        yield from PermissionMapper::toDomainIterable($this->all($query));
    }

    public function findPermissionById(PermissionId $permissionId): ?DomainPermission
    {
        /** @var EloquentPermission|null $model */
        $model = $this->show($permissionId->get('value'), ['roles', 'users']);

        return $model ? PermissionMapper::toDomain($model) : null;
    }

    public function findPermissionByName(PermissionName $name): ?DomainPermission
    {
        $query = RepositoryQuery::fromOptions([
            'filters' => ['name' => $name->get('value')],
            'include' => ['roles', 'users'],
        ]);

        $model = $this->buildQuery($query)->first();

        return $model instanceof EloquentPermission ? PermissionMapper::toDomain($model) : null;
    }

    public function savePermission(DomainPermission $permission): void
    {
        /** @var EloquentPermission $model */
        $model = $this->create(PermissionMapper::toEloquent($permission));
        $this->syncRelations($model, $permission);
    }

    public function updatePermission(DomainPermission $permission): void
    {
        $model = $this->show($permission->getId()->get('value'), ['roles', 'users']);
        if ($model === null) {
            return;
        }

        $updated = PermissionMapper::toUpdatedEloquent($model, $permission);
        $this->update($updated);
        $this->syncRelations($updated, $permission);
    }

    public function deletePermission(DomainPermission $permission): void
    {
        $model = $this->show($permission->getId()->get('value'), ['roles', 'users']);
        if ($model === null) {
            return;
        }

        $model->roles()->detach();
        $model->users()->detach();
        $this->delete($model);
    }

    private function syncRelations(EloquentPermission $model, DomainPermission $permission): void
    {
        $roleIds = array_map(
            fn ($roleId) => $roleId->get('value'),
            $permission->getRoleIds()
        );

        $userIds = array_map(
            fn ($userId) => $userId->get('value'),
            $permission->getUserIds()
        );

        $model->roles()->sync($roleIds);
        $model->users()->sync($userIds);
    }
}
