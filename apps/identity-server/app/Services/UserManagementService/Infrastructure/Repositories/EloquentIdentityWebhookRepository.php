<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityWebhook as DomainIdentityWebhook;
use App\Services\UserManagementService\Domain\Repositories\IdentityWebhookRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityWebhookId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityWebhookMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint;

final class EloquentIdentityWebhookRepository implements IdentityWebhookRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityWebhookId $webhookId,
    ): ?DomainIdentityWebhook {
        $model = IdentityWebhookEndpoint::query()
            ->where('project_id', $projectId->toString())
            ->find($webhookId->toString());

        return $model ? IdentityWebhookMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityWebhook $webhook): void
    {
        $model = IdentityWebhookEndpoint::query()->find($webhook->id()->toString())
            ?? new IdentityWebhookEndpoint;

        IdentityWebhookMapper::fill($model, $webhook)->save();
    }

    public function delete(DomainIdentityWebhook $webhook): void
    {
        IdentityWebhookEndpoint::query()->whereKey($webhook->id()->toString())->delete();
    }
}
