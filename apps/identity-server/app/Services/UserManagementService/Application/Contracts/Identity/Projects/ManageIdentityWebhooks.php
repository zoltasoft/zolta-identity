<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ManageIdentityWebhooks
{
    /** @param list<string> $events @return array<string, mixed> */
    public function createWebhook(string $actorUserId, string $projectId, string $url, array $events): array;

    /** @param list<string> $events */
    public function updateWebhook(
        string $actorUserId,
        string $projectId,
        string $webhookId,
        string $url,
        array $events,
        string $status,
    ): void;

    /** @return array<string, mixed> */
    public function rotateWebhookSecret(string $actorUserId, string $projectId, string $webhookId): array;

    public function removeWebhook(string $actorUserId, string $projectId, string $webhookId): void;
}
