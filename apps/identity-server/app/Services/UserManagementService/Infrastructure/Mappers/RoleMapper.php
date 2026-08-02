<?php

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\Role as DomainRole;
use App\Services\UserManagementService\Domain\Factories\PermissionFactory;
use App\Services\UserManagementService\Domain\Factories\RoleFactory;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Role as EloquentRole;
use Zolta\Cqrs\Repositories\Mapper\RepositoryMapper;

class RoleMapper implements RepositoryMapper
{
    /**
     * Map a domain entity to a persistence model or array.
     */
    public static function toPersistence(object $entity): object|array
    {
        if ($entity instanceof DomainRole) {
            return self::toEloquent($entity);
        }
        throw new \InvalidArgumentException('Unsupported entity type for toPersistence in RoleMapper');
    }

    /**
     * Map an iterable of EloquentRole models to DomainRole aggregates (generator).
     *
     * @param  iterable<EloquentRole>  $models
     * @return \Generator<int, DomainRole>
     */
    public static function toDomainIterable(iterable $models): \Generator
    {
        foreach ($models as $model) {
            yield self::toDomain($model);
        }
    }

    /**
     * Map an iterable of DomainRole aggregates to EloquentRole models (generator).
     *
     * @param  iterable<DomainRole>  $roles
     * @return \Generator<int, EloquentRole>
     */
    public static function toEloquentIterable(iterable $roles): \Generator
    {
        foreach ($roles as $role) {
            yield self::toEloquent($role);
        }
    }

    /**
     * Convert an EloquentRole model to a DomainRole aggregate.
     */
    public static function toDomain(object $model): object
    {
        if (! ($model instanceof EloquentRole)) {
            throw new \InvalidArgumentException('Expected EloquentRole in toDomain');
        }
        $permissionFactory = new PermissionFactory;
        $roleFactory = new RoleFactory;

        $roleData = $model->toArray();

        $permissionRows = [];
        if ($model->relationLoaded('permissions')) {
            foreach ($model->permissions as $permissionModel) {
                $permissionRows[] = $permissionModel->toArray();
            }
        }

        $userRows = [];
        if ($model->relationLoaded('users')) {
            foreach ($model->users as $userModel) {
                $userRows[] = ['id' => (string) $userModel->id];
            }
        }

        return $roleFactory->restoreFromRow(
            $roleData,
            $permissionRows,
            $permissionFactory,
            $userRows
        );
    }

    /**
     * Convert a DomainRole aggregate to an EloquentRole model.
     */
    public static function toEloquent(DomainRole $role): EloquentRole
    {
        $attrs = [
            'id' => (string) $role->getId()->get('value'),
            'role' => (string) $role->getName()->get('value'),
            'description' => $role->getDescription()?->get('description'),
            'created_at' => $role->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $role->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return new EloquentRole($attrs);
    }

    /**
     * Update an existing EloquentRole model using a DomainRole aggregate.
     */
    public static function toUpdatedEloquent(EloquentRole $model, DomainRole $role): EloquentRole
    {
        $model->role = (string) $role->getName()->get('value');
        $model->description = $role->getDescription()?->get('description');

        return $model;
    }
}
