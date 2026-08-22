<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityClient as DomainIdentityClient;
use App\Services\UserManagementService\Domain\Repositories\IdentityClientRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityClientMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;

final class EloquentIdentityClientRepository implements IdentityClientRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityClientId $clientId,
    ): ?DomainIdentityClient {
        $model = IdentityProjectClient::query()
            ->where('project_id', $projectId->toString())
            ->find($clientId->toString());

        return $model ? IdentityClientMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityClient $client): void
    {
        $model = IdentityProjectClient::query()->find($client->id()->toString())
            ?? new IdentityProjectClient;

        IdentityClientMapper::fill($model, $client)->save();
    }

    public function delete(DomainIdentityClient $client): void
    {
        IdentityProjectClient::query()->whereKey($client->id()->toString())->delete();
    }
}
