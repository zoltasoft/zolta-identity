<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityClient as DomainIdentityClient;
use App\Services\UserManagementService\Domain\Repositories\IdentityClientRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityClientMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryConstraint;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;

final class EloquentIdentityClientRepository extends BaseRepository implements IdentityClientRepository
{
    protected array $allowedConstraintFields = ['id', 'project_id'];

    protected bool $enableReadCaching = false;

    protected function modelClass(): string
    {
        return IdentityProjectClient::class;
    }

    public function findForProject(
        IdentityProjectId $projectId,
        IdentityClientId $clientId,
    ): ?DomainIdentityClient {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $clientId->toString()),
            RepositoryConstraint::equals('project_id', $projectId->toString()),
        ));

        return $model instanceof IdentityProjectClient ? IdentityClientMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityClient $client): void
    {
        $existing = $this->show($client->id()->toString());
        $model = $existing instanceof IdentityProjectClient ? $existing : new IdentityProjectClient;

        $model = IdentityClientMapper::fill($model, $client);
        $existing instanceof IdentityProjectClient ? $this->update($model) : $this->create($model);
    }

    public function remove(DomainIdentityClient $client): void
    {
        $model = $this->findForProject($client->projectId(), $client->id());
        if ($model !== null) {
            $persisted = $this->show($model->id()->toString());
            if ($persisted instanceof IdentityProjectClient) {
                parent::delete($persisted);
            }
        }
    }
}
