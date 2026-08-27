<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityWebhook as DomainIdentityWebhook;
use App\Services\UserManagementService\Domain\Repositories\IdentityWebhookRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityWebhookId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityWebhookMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint;
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryConstraint;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;

final class EloquentIdentityWebhookRepository extends BaseRepository implements IdentityWebhookRepository
{
    protected array $allowedConstraintFields = ['id', 'project_id'];

    protected bool $enableReadCaching = false;

    protected function modelClass(): string
    {
        return IdentityWebhookEndpoint::class;
    }

    public function findForProject(
        IdentityProjectId $projectId,
        IdentityWebhookId $webhookId,
    ): ?DomainIdentityWebhook {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $webhookId->toString()),
            RepositoryConstraint::equals('project_id', $projectId->toString()),
        ));

        return $model instanceof IdentityWebhookEndpoint ? IdentityWebhookMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityWebhook $webhook): void
    {
        $existing = $this->show($webhook->id()->toString());
        $model = $existing instanceof IdentityWebhookEndpoint ? $existing : new IdentityWebhookEndpoint;

        $model = IdentityWebhookMapper::fill($model, $webhook);
        $existing instanceof IdentityWebhookEndpoint ? $this->update($model) : $this->create($model);
    }

    public function remove(DomainIdentityWebhook $webhook): void
    {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $webhook->id()->toString()),
            RepositoryConstraint::equals('project_id', $webhook->projectId()->toString()),
        ));
        if ($model instanceof IdentityWebhookEndpoint) {
            parent::delete($model);
        }
    }
}
