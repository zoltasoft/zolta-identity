<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

interface IdentityInstallationServiceInterface
{
    /** @return list<array<string, mixed>> */
    public function listInstallationUsers(string $actorUserId): array;

    public function updateInstallationUser(string $actorUserId, string $userId, bool $isSystemAdmin, bool $locked): void;
}
