<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\IdentityWebhook as DomainIdentityWebhook;
use App\Services\UserManagementService\Domain\Enums\IdentityWebhookStatus;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityWebhookId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint as EloquentIdentityWebhook;
use DateTimeImmutable;

final class IdentityWebhookMapper
{
    public static function toDomain(EloquentIdentityWebhook $model): DomainIdentityWebhook
    {
        return DomainIdentityWebhook::reconstitute(
            IdentityWebhookId::fromString((string) $model->id),
            IdentityProjectId::fromString((string) $model->project_id),
            (string) $model->url,
            array_values((array) $model->events),
            (string) $model->secret,
            (string) $model->secret_prefix,
            IdentityWebhookStatus::from((string) $model->status),
            $model->last_delivered_at
                ? DateTimeImmutable::createFromInterface($model->last_delivered_at)
                : null,
            DateTimeImmutable::createFromInterface($model->created_at),
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    public static function fill(
        EloquentIdentityWebhook $model,
        DomainIdentityWebhook $webhook,
    ): EloquentIdentityWebhook {
        return $model->forceFill([
            'id' => $webhook->id()->toString(),
            'project_id' => $webhook->projectId()->toString(),
            'url' => $webhook->url(),
            'events' => $webhook->events(),
            'secret' => $webhook->secret(),
            'secret_prefix' => $webhook->secretPrefix(),
            'status' => $webhook->status()->value,
            'last_delivered_at' => $webhook->lastDeliveredAt(),
            'created_at' => $webhook->createdAt(),
            'updated_at' => $webhook->updatedAt(),
        ]);
    }
}
