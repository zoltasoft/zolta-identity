<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\IdentityProject as DomainIdentityProject;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectRegistrationMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectStatus;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject as EloquentIdentityProject;
use DateTimeImmutable;

final class IdentityProjectMapper
{
    public static function toDomain(EloquentIdentityProject $model): DomainIdentityProject
    {
        return DomainIdentityProject::reconstitute(
            IdentityProjectId::fromString((string) $model->id),
            (string) $model->name,
            (string) $model->slug,
            $model->description !== null ? (string) $model->description : null,
            IdentityProjectStatus::from((string) $model->status),
            IdentityProjectMode::from((string) $model->mode),
            (int) $model->sandbox_ttl_minutes,
            IdentityProjectRegistrationMode::from((string) $model->registration_mode),
            $model->registration_role_id !== null ? (string) $model->registration_role_id : null,
            (bool) $model->email_verification_required,
            $model->deletion_scheduled_at !== null ? DateTimeImmutable::createFromInterface($model->deletion_scheduled_at) : null,
            $model->deletion_previous_status !== null ? IdentityProjectStatus::from((string) $model->deletion_previous_status) : null,
            DateTimeImmutable::createFromInterface($model->created_at),
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    public static function fill(
        EloquentIdentityProject $model,
        DomainIdentityProject $project,
    ): EloquentIdentityProject {
        return $model->forceFill([
            'id' => $project->id()->toString(),
            'name' => $project->name(),
            'slug' => $project->slug(),
            'description' => $project->description(),
            'status' => $project->status()->value,
            'mode' => $project->mode()->value,
            'sandbox_ttl_minutes' => $project->sandboxTtlMinutes(),
            'registration_mode' => $project->registrationMode()->value,
            'registration_role_id' => $project->registrationRoleId(),
            'email_verification_required' => $project->emailVerificationRequired(),
            'deletion_scheduled_at' => $project->deletionScheduledAt(),
            'deletion_previous_status' => $project->deletionPreviousStatus()?->value,
            'created_at' => $project->createdAt(),
            'updated_at' => $project->updatedAt(),
        ]);
    }
}
