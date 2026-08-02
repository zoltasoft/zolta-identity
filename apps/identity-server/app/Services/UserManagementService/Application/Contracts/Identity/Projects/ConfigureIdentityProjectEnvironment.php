<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ConfigureIdentityProjectEnvironment
{
    public function updateProjectEnvironment(
        string $actorUserId,
        string $projectId,
        string $mode,
        int $sandboxTtlMinutes,
    ): void;
}
