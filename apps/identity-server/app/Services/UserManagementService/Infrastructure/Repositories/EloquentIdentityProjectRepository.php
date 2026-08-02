<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityProject as DomainIdentityProject;
use App\Services\UserManagementService\Domain\Repositories\IdentityProjectRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityProjectMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject as EloquentIdentityProject;

final class EloquentIdentityProjectRepository implements IdentityProjectRepository
{
    public function find(IdentityProjectId $projectId): ?DomainIdentityProject
    {
        $model = EloquentIdentityProject::query()->find($projectId->toString());

        return $model ? IdentityProjectMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityProject $project): void
    {
        $model = EloquentIdentityProject::query()->find($project->id()->toString())
            ?? new EloquentIdentityProject;

        IdentityProjectMapper::fill($model, $project)->save();
    }
}
