<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

interface IdentityLifecyclePublisherInterface
{
    public function requestUserDeletion(string $userId, string $email): bool;
}
