<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use App\Services\UserManagementService\Domain\Enums\IdentityWebhookStatus;
use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityWebhookConfigurationException;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityWebhookId;
use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;

final class IdentityWebhook extends AggregateRoot
{
    /** @param list<string> $events */
    private function __construct(
        private readonly IdentityWebhookId $id,
        private readonly IdentityProjectId $projectId,
        private string $url,
        private array $events,
        private string $secret,
        private string $secretPrefix,
        private IdentityWebhookStatus $status,
        private ?DateTimeImmutable $lastDeliveredAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->events = $this->normalizeEvents($events);
        $this->assertSecret($secret, $secretPrefix);
    }

    /** @param list<string> $events */
    public static function create(
        IdentityProjectId $projectId,
        string $url,
        array $events,
        string $secret,
        string $secretPrefix,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            new IdentityWebhookId,
            $projectId,
            $url,
            $events,
            $secret,
            $secretPrefix,
            IdentityWebhookStatus::Active,
            null,
            $now,
            $now,
        );
    }

    /** @param list<string> $events */
    public static function reconstitute(
        IdentityWebhookId $id,
        IdentityProjectId $projectId,
        string $url,
        array $events,
        string $secret,
        string $secretPrefix,
        IdentityWebhookStatus $status,
        ?DateTimeImmutable $lastDeliveredAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $projectId,
            $url,
            $events,
            $secret,
            $secretPrefix,
            $status,
            $lastDeliveredAt,
            $createdAt,
            $updatedAt,
        );
    }

    /** @param list<string> $events */
    public function configure(string $url, array $events, IdentityWebhookStatus $status): void
    {
        $this->url = $url;
        $this->events = $this->normalizeEvents($events);
        $this->status = $status;
        $this->touch();
    }

    public function rotateSecret(string $secret, string $secretPrefix): void
    {
        $this->assertSecret($secret, $secretPrefix);
        $this->secret = $secret;
        $this->secretPrefix = $secretPrefix;
        $this->touch();
    }

    public function id(): IdentityWebhookId
    {
        return $this->id;
    }

    public function projectId(): IdentityProjectId
    {
        return $this->projectId;
    }

    public function url(): string
    {
        return $this->url;
    }

    /** @return list<string> */
    public function events(): array
    {
        return $this->events;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function secretPrefix(): string
    {
        return $this->secretPrefix;
    }

    public function status(): IdentityWebhookStatus
    {
        return $this->status;
    }

    public function lastDeliveredAt(): ?DateTimeImmutable
    {
        return $this->lastDeliveredAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @param list<string> $events @return list<string> */
    private function normalizeEvents(array $events): array
    {
        $events = array_values(array_unique($events));
        if ($events === []) {
            throw new InvalidIdentityWebhookConfigurationException(
                'A webhook must subscribe to at least one event.',
            );
        }

        return $events;
    }

    private function assertSecret(string $secret, string $secretPrefix): void
    {
        if ($secret === '' || $secretPrefix === '') {
            throw new InvalidIdentityWebhookConfigurationException(
                'Webhook signing credentials are malformed.',
            );
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
