<?php

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\Permission as DomainPermission;
use App\Services\UserManagementService\Domain\Factories\PermissionFactory;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Permission as EloquentPermission;
use Zolta\Cqrs\Repositories\Mapper\RepositoryMapper;

class PermissionMapper implements RepositoryMapper
{
    /**
     * Map a domain entity to a persistence model or array.
     */
    public static function toPersistence(object $entity): object|array
    {
        if ($entity instanceof DomainPermission) {
            return self::toEloquent($entity);
        }
        throw new \InvalidArgumentException('Unsupported entity type for toPersistence in PermissionMapper');
    }

    /**
     * Map an iterable of EloquentPermission models to DomainPermission aggregates (generator).
     *
     * @param  iterable<EloquentPermission>  $models
     * @return \Generator<int, DomainPermission>
     */
    public static function toDomainIterable(iterable $models): \Generator
    {
        foreach ($models as $model) {
            yield self::toDomain($model);
        }
    }

    /**
     * Map an iterable of DomainPermission aggregates to EloquentPermission models (generator).
     *
     * @param  iterable<DomainPermission>  $permissions
     * @return \Generator<int, EloquentPermission>
     */
    public static function toEloquentIterable(iterable $permissions): \Generator
    {
        foreach ($permissions as $permission) {
            yield self::toEloquent($permission);
        }
    }

    public static function toDomain(object $model): object
    {
        if (! ($model instanceof EloquentPermission)) {
            throw new \InvalidArgumentException('Expected EloquentPermission in toDomain');
        }
        $factory = new PermissionFactory;

        $roleData = [];
        if ($model->relationLoaded('roles')) {
            foreach ($model->roles as $roleModel) {
                $roleData[] = ['id' => (string) $roleModel->id];
            }
        }

        $userData = [];
        if ($model->relationLoaded('users')) {
            foreach ($model->users as $userModel) {
                $userData[] = ['id' => (string) $userModel->id];
            }
        }

        return $factory->restoreFromRow(
            $model->toArray(),
            $roleData,
            $userData
        );
    }

    public static function toEloquent(DomainPermission $permission): EloquentPermission
    {
        $attrs = [
            'id' => (string) $permission->getId()->get('value'),
            'name' => (string) $permission->getName()->get('value'),
            'description' => $permission->getDescription()?->get('description'),
            'created_at' => $permission->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $permission->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return new EloquentPermission($attrs);
    }

    public static function toUpdatedEloquent(EloquentPermission $model, DomainPermission $permission): EloquentPermission
    {
        $model->name = (string) $permission->getName()->get('value');
        $model->description = $permission->getDescription()?->get('description');

        return $model;
    }
}
