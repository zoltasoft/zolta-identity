<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ManageIdentityProjectSuspension
{
    /** @return array<string, mixed> */
    public function suspendProject(string $actorUserId, string $projectId, string $confirmation): array;

    /** @return array<string, mixed> */
    public function reactivateProject(string $actorUserId, string $projectId): array;
}
