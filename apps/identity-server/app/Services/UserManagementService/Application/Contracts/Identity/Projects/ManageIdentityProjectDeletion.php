<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ManageIdentityProjectDeletion
{
    /** @return array<string, mixed> */
    public function scheduleProjectDeletion(string $actorUserId, string $projectId, string $confirmation): array;

    /** @return array<string, mixed> */
    public function cancelProjectDeletion(string $actorUserId, string $projectId): array;
}
