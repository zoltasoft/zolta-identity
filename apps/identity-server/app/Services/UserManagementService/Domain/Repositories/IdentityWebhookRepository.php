<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityWebhook;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityWebhookId;

interface IdentityWebhookRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityWebhookId $webhookId,
    ): ?IdentityWebhook;

    public function save(IdentityWebhook $webhook): void;

    public function remove(IdentityWebhook $webhook): void;
}
