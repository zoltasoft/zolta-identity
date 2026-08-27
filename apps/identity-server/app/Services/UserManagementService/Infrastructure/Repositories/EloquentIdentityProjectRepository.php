<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityProject as DomainIdentityProject;
use App\Services\UserManagementService\Domain\Repositories\IdentityProjectRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityProjectMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject as EloquentIdentityProject;
use Zolta\Cqrs\Repositories\BaseRepository;

final class EloquentIdentityProjectRepository extends BaseRepository implements IdentityProjectRepository
{
    protected bool $enableReadCaching = false;

    protected function modelClass(): string
    {
        return EloquentIdentityProject::class;
    }

    public function find(IdentityProjectId $projectId): ?DomainIdentityProject
    {
        $model = $this->show($projectId->toString());

        return $model instanceof EloquentIdentityProject ? IdentityProjectMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityProject $project): void
    {
        $existing = $this->show($project->id()->toString());
        $model = $existing instanceof EloquentIdentityProject ? $existing : new EloquentIdentityProject;

        $model = IdentityProjectMapper::fill($model, $project);
        $existing instanceof EloquentIdentityProject ? $this->update($model) : $this->create($model);
    }
}
