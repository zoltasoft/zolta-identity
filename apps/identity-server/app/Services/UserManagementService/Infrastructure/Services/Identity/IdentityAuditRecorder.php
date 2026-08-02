<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityAuditEvent;

final class IdentityAuditRecorder
{
    /** @param array<string, mixed> $metadata */
    public function record(
        string $event,
        ?string $projectId,
        ?string $clientId,
        ?string $actorUserId,
        ?string $targetType,
        ?string $targetId,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        IdentityAuditEvent::query()->create([
            'project_id' => $projectId,
            'client_id' => $clientId,
            'actor_user_id' => $actorUserId,
            'event' => $event,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata ?: null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
