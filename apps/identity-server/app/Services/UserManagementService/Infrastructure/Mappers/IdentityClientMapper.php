<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\IdentityClient as DomainIdentityClient;
use App\Services\UserManagementService\Domain\Enums\IdentityClientStatus;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient as EloquentIdentityClient;
use DateTimeImmutable;

final class IdentityClientMapper
{
    public static function toDomain(EloquentIdentityClient $model): DomainIdentityClient
    {
        return DomainIdentityClient::reconstitute(
            IdentityClientId::fromString((string) $model->id),
            IdentityProjectId::fromString((string) $model->project_id),
            (string) $model->name,
            (string) $model->secret_hash,
            (string) $model->secret_prefix,
            IdentityClientStatus::from((string) $model->status),
            $model->last_used_at
                ? DateTimeImmutable::createFromInterface($model->last_used_at)
                : null,
            DateTimeImmutable::createFromInterface($model->created_at),
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    public static function fill(
        EloquentIdentityClient $model,
        DomainIdentityClient $client,
    ): EloquentIdentityClient {
        return $model->forceFill([
            'id' => $client->id()->toString(),
            'project_id' => $client->projectId()->toString(),
            'name' => $client->name(),
            'secret_hash' => $client->secretHash(),
            'secret_prefix' => $client->secretPrefix(),
            'status' => $client->status()->value,
            'last_used_at' => $client->lastUsedAt(),
            'created_at' => $client->createdAt(),
            'updated_at' => $client->updatedAt(),
        ]);
    }
}
