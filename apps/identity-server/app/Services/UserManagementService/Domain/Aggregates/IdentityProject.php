<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use App\Services\UserManagementService\Domain\Enums\IdentityProjectMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectRegistrationMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectStatus;
use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityProjectConfigurationException;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;

final class IdentityProject extends AggregateRoot
{
    private function __construct(
        private readonly IdentityProjectId $id,
        private string $name,
        private string $slug,
        private ?string $description,
        private IdentityProjectStatus $status,
        private IdentityProjectMode $mode,
        private int $sandboxTtlMinutes,
        private IdentityProjectRegistrationMode $registrationMode,
        private ?string $registrationRoleId,
        private bool $emailVerificationRequired,
        private ?DateTimeImmutable $deletionScheduledAt,
        private ?IdentityProjectStatus $deletionPreviousStatus,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->assertSandboxTtl($sandboxTtlMinutes);
    }

    public static function create(string $name, string $slug, ?string $description = null): self
    {
        $now = new DateTimeImmutable;

        return new self(
            new IdentityProjectId,
            $name,
            $slug,
            $description,
            IdentityProjectStatus::Active,
            IdentityProjectMode::Live,
            60,
            IdentityProjectRegistrationMode::InviteOnly,
            null,
            true,
            null,
            null,
            $now,
            $now,
        );
    }

    public static function reconstitute(
        IdentityProjectId $id,
        string $name,
        string $slug,
        ?string $description,
        IdentityProjectStatus $status,
        IdentityProjectMode $mode,
        int $sandboxTtlMinutes,
        IdentityProjectRegistrationMode $registrationMode,
        ?string $registrationRoleId,
        bool $emailVerificationRequired,
        ?DateTimeImmutable $deletionScheduledAt,
        ?IdentityProjectStatus $deletionPreviousStatus,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $name,
            $slug,
            $description,
            $status,
            $mode,
            $sandboxTtlMinutes,
            $registrationMode,
            $registrationRoleId,
            $emailVerificationRequired,
            $deletionScheduledAt,
            $deletionPreviousStatus,
            $createdAt,
            $updatedAt,
        );
    }

    public function configureRegistration(
        IdentityProjectRegistrationMode $mode,
        ?string $roleId,
        bool $emailVerificationRequired,
    ): void {
        if ($mode === $this->registrationMode
            && $roleId === $this->registrationRoleId
            && $emailVerificationRequired === $this->emailVerificationRequired) {
            return;
        }

        $this->registrationMode = $mode;
        $this->registrationRoleId = $roleId;
        $this->emailVerificationRequired = $emailVerificationRequired;
        $this->touch();
    }

    public function configureEnvironment(IdentityProjectMode $mode, int $sandboxTtlMinutes): void
    {
        $this->assertSandboxTtl($sandboxTtlMinutes);

        if ($mode === $this->mode && $sandboxTtlMinutes === $this->sandboxTtlMinutes) {
            return;
        }

        $this->mode = $mode;
        $this->sandboxTtlMinutes = $sandboxTtlMinutes;
        $this->touch();
    }

    public function scheduleDeletion(DateTimeImmutable $scheduledAt): void
    {
        if ($this->status === IdentityProjectStatus::PendingDeletion) {
            return;
        }

        $this->deletionPreviousStatus = $this->status;
        $this->status = IdentityProjectStatus::PendingDeletion;
        $this->deletionScheduledAt = $scheduledAt;
        $this->touch();
    }

    public function suspend(): bool
    {
        if ($this->status === IdentityProjectStatus::Suspended) {
            return false;
        }

        if ($this->status === IdentityProjectStatus::PendingDeletion) {
            throw new InvalidIdentityProjectConfigurationException(
                'A project scheduled for deletion must have its deletion cancelled before it can be suspended.',
            );
        }

        $this->status = IdentityProjectStatus::Suspended;
        $this->touch();

        return true;
    }

    public function reactivate(): bool
    {
        if ($this->status === IdentityProjectStatus::Active) {
            return false;
        }

        if ($this->status === IdentityProjectStatus::PendingDeletion) {
            throw new InvalidIdentityProjectConfigurationException(
                'A project scheduled for deletion must have its deletion cancelled before it can be reactivated.',
            );
        }

        $this->status = IdentityProjectStatus::Active;
        $this->touch();

        return true;
    }

    public function cancelDeletion(): void
    {
        if ($this->status !== IdentityProjectStatus::PendingDeletion || $this->deletionPreviousStatus === null) {
            throw new InvalidIdentityProjectConfigurationException('This project is not scheduled for deletion.');
        }

        $this->status = $this->deletionPreviousStatus;
        $this->deletionPreviousStatus = null;
        $this->deletionScheduledAt = null;
        $this->touch();
    }

    public function id(): IdentityProjectId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function status(): IdentityProjectStatus
    {
        return $this->status;
    }

    public function deletionScheduledAt(): ?DateTimeImmutable
    {
        return $this->deletionScheduledAt;
    }

    public function deletionPreviousStatus(): ?IdentityProjectStatus
    {
        return $this->deletionPreviousStatus;
    }

    public function mode(): IdentityProjectMode
    {
        return $this->mode;
    }

    public function sandboxTtlMinutes(): int
    {
        return $this->sandboxTtlMinutes;
    }

    public function registrationMode(): IdentityProjectRegistrationMode
    {
        return $this->registrationMode;
    }

    public function registrationRoleId(): ?string
    {
        return $this->registrationRoleId;
    }

    public function emailVerificationRequired(): bool
    {
        return $this->emailVerificationRequired;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function assertSandboxTtl(int $sandboxTtlMinutes): void
    {
        if ($sandboxTtlMinutes < 5 || $sandboxTtlMinutes > 1440) {
            throw new InvalidIdentityProjectConfigurationException(
                'Sandbox session lifetime must be between 5 and 1440 minutes.',
            );
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
