<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

use Zolta\Domain\ValueObjects\UserId;

interface AccountSecurityServiceInterface
{
    public function listSessions(UserId $userId, ?int $currentTokenId = null): array;

    public function revokeSession(UserId $userId, int $tokenId): void;

    public function changePassword(
        UserId $userId,
        string $currentPassword,
        string $newPassword,
        ?int $currentTokenId = null,
    ): void;
}
