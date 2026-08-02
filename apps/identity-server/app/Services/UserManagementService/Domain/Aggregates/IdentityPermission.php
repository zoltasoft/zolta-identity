<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use App\Services\UserManagementService\Domain\Enums\IdentityPermissionSource;
use App\Services\UserManagementService\Domain\Enums\IdentityPermissionStatus;
use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityAccessConfigurationException;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;

final class IdentityPermission extends AggregateRoot
{
    private function __construct(
        private readonly IdentityPermissionId $id,
        private readonly IdentityProjectId $projectId,
        private ?IdentityClientId $sourceClientId,
        private readonly string $key,
        private string $name,
        private ?string $description,
        private IdentityPermissionSource $source,
        private IdentityPermissionStatus $status,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->assertIdentity($key, $name);
        $this->assertSource();
    }

    public static function createManual(
        IdentityProjectId $projectId,
        string $key,
        string $name,
        ?string $description = null,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            new IdentityPermissionId,
            $projectId,
            null,
            $key,
            $name,
            $description,
            IdentityPermissionSource::Manual,
            IdentityPermissionStatus::Active,
            $now,
            $now,
        );
    }

    public static function createFromManifest(
        IdentityProjectId $projectId,
        IdentityClientId $clientId,
        string $key,
        string $name,
        ?string $description = null,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            new IdentityPermissionId,
            $projectId,
            $clientId,
            $key,
            $name,
            $description,
            IdentityPermissionSource::Manifest,
            IdentityPermissionStatus::Active,
            $now,
            $now,
        );
    }

    public static function reconstitute(
        IdentityPermissionId $id,
        IdentityProjectId $projectId,
        ?IdentityClientId $sourceClientId,
        string $key,
        string $name,
        ?string $description,
        IdentityPermissionSource $source,
        IdentityPermissionStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $projectId,
            $sourceClientId,
            $key,
            $name,
            $description,
            $source,
            $status,
            $createdAt,
            $updatedAt,
        );
    }

    public function synchronizeFromManifest(
        IdentityClientId $clientId,
        string $name,
        ?string $description,
    ): void {
        $this->sourceClientId = $clientId;
        $this->name = $name;
        $this->description = $description;
        $this->source = IdentityPermissionSource::Manifest;
        $this->status = IdentityPermissionStatus::Active;
        $this->assertIdentity($this->key, $name);
        $this->touch();
    }

    public function markStale(): void
    {
        if ($this->status === IdentityPermissionStatus::Stale) {
            return;
        }

        $this->status = IdentityPermissionStatus::Stale;
        $this->touch();
    }

    public function id(): IdentityPermissionId
    {
        return $this->id;
    }

    public function projectId(): IdentityProjectId
    {
        return $this->projectId;
    }

    public function sourceClientId(): ?IdentityClientId
    {
        return $this->sourceClientId;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function source(): IdentityPermissionSource
    {
        return $this->source;
    }

    public function status(): IdentityPermissionStatus
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function assertIdentity(string $key, string $name): void
    {
        if (preg_match('/^[a-z0-9]+(?:[._:-][a-z0-9]+)*$/', $key) !== 1
            || trim($name) === '') {
            throw new InvalidIdentityAccessConfigurationException(
                'Permission key or name is malformed.',
            );
        }
    }

    private function assertSource(): void
    {
        if (($this->source === IdentityPermissionSource::Manifest)
            !== ($this->sourceClientId !== null)) {
            throw new InvalidIdentityAccessConfigurationException(
                'Manifest permissions require a source client.',
            );
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
