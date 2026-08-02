<?php

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\Role as DomainRole;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use App\Services\UserManagementService\Infrastructure\Mappers\RoleMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Role as EloquentRole;
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;
use Zolta\Domain\Repositories\Query\AbstractQueryOptions;
use Zolta\Domain\ValueObjects\Pagination;
use Zolta\Domain\ValueObjects\RoleId;
use Zolta\Domain\ValueObjects\RoleName;

class EloquentRoleRepository extends BaseRepository implements RoleRepository
{
    protected array $allowedFilters = ['id', 'role'];

    protected array $filterOperators = [
        'eq' => '=',
        'like' => 'like',
        'in' => 'in',
        'not_in' => 'not in',
    ];

    protected function modelClass(): string
    {
        return EloquentRole::class;
    }

    protected array $allowedRelations = ['permissions', 'users'];

    public function getAllRoles(AbstractQueryOptions $options): iterable
    {
        $query = $this->query($options);
        yield from RoleMapper::toDomainIterable($this->all($query));
    }

    public function findRoleById(RoleId $id, ?AbstractQueryOptions $options = null): ?DomainRole
    {
        $query = $this->repositoryQuery($options);
        $filters = $query->filters();
        $filters['id'] = $id->value;

        $model = $this->first(RepositoryQuery::fromOptions([
            'filters' => $filters,
            'include' => $query->includes(),
            'sort' => $query->sort(),
            'context' => $query->context(),
        ]));

        return $model ? RoleMapper::toDomain($model) : null;
    }

    public function findRoleByName(RoleName $name, ?AbstractQueryOptions $options = null): ?DomainRole
    {
        $query = $this->repositoryQuery($options);
        $filters = $query->filters();
        $filters['role'] = $name->get('value');

        $model = $this->first(RepositoryQuery::fromOptions([
            'filters' => $filters,
            'include' => $query->includes(),
            'sort' => $query->sort(),
            'context' => $query->context(),
        ]));

        return $model ? RoleMapper::toDomain($model) : null;
    }

    public function saveRole(DomainRole $role): void
    {
        /** @var EloquentRole $model */
        $model = $this->create(RoleMapper::toEloquent($role));
        $this->syncPermissions($model, $role);
    }

    public function updateRole(DomainRole $role): void
    {
        $model = $this->show($role->getId()->get('value'), ['permissions']);
        if ($model) {
            $updated = RoleMapper::toUpdatedEloquent($model, $role);
            $this->update($updated);
            $this->syncPermissions($updated, $role);
        }
    }

    public function deleteRole(DomainRole $role): void
    {
        $model = $this->show($role->getId()->get('value'));
        if ($model) {
            $model->permissions()->detach();
            $this->delete($model);
        }
    }

    public function findRolesWithPermissions(): iterable
    {
        $query = $this->query(['include' => ['permissions']]);
        yield from RoleMapper::toDomainIterable($this->all($query));
    }

    public function findRolesPaginated(AbstractQueryOptions $options): Pagination
    {
        $paginator = $this->paginate($options);
        $domainItems = iterator_to_array(RoleMapper::toDomainIterable($paginator->items()));

        return new Pagination(
            items: $domainItems,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage()
        );
    }

    private function syncPermissions(EloquentRole $model, DomainRole $role): void
    {
        $permissionIds = array_map(
            fn ($permission) => $permission->getId()->get('value'),
            $role->getPermissions()
        );

        $model->permissions()->sync($permissionIds);
    }
}
