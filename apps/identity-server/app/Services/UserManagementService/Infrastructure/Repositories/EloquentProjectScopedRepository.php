<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryConstraint;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;

/**
 * @template TModel of Model
 */
abstract class EloquentProjectScopedRepository extends BaseRepository
{
    protected array $allowedConstraintFields = ['id', 'project_id'];

    protected bool $enableReadCaching = false;

    /**
     * @param  list<string>  $includes
     * @param  list<string>  $sort
     * @return Collection<int, TModel>
     */
    public function listForProject(
        string $projectId,
        array $includes = [],
        array $sort = [],
        ?int $limit = null,
    ): Collection {
        $query = RepositoryQuery::fromOptions([
            'include' => $includes,
            'sort' => $sort,
            'limit' => $limit,
        ])->withConstraint(RepositoryConstraint::equals('project_id', $projectId));

        /** @var Collection<int, TModel> $models */
        $models = collect($this->all($query));

        return $models;
    }

    /** @return TModel|null */
    public function findForProject(string $projectId, string $modelId, array $includes = []): ?Model
    {
        $query = RepositoryQuery::fromOptions(['include' => $includes])->withConstraints(
            RepositoryConstraint::equals('id', $modelId),
            RepositoryConstraint::equals('project_id', $projectId),
        );

        /** @var TModel|null $model */
        $model = $this->first($query);

        return $model;
    }

    /** @return TModel */
    public function findForProjectOrFail(string $projectId, string $modelId, array $includes = []): Model
    {
        $model = $this->findForProject($projectId, $modelId, $includes);
        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($this->modelClass(), [$modelId]);
        }

        return $model;
    }

    /**
     * @param  list<string>  $modelIds
     * @return list<string>
     */
    public function existingIdsForProject(string $projectId, array $modelIds): array
    {
        $uniqueIds = array_values(array_unique($modelIds));
        if ($uniqueIds === []) {
            return [];
        }

        $query = RepositoryQuery::fromOptions(['fields' => ['id']])->withConstraints(
            RepositoryConstraint::equals('project_id', $projectId),
            RepositoryConstraint::oneOf('id', $uniqueIds),
        );

        return collect($this->all($query))->pluck('id')->map(static fn (mixed $id): string => (string) $id)->all();
    }

    public function existsForProject(string $projectId, string $modelId): bool
    {
        return $this->findForProject($projectId, $modelId) !== null;
    }
}
