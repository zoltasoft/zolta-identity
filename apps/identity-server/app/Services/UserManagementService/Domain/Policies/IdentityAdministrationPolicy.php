<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Policies;

final class IdentityAdministrationPolicy
{
    public function canUpdateInstallationAccount(
        string $actorUserId,
        string $targetUserId,
        bool $targetIsSystemAdministrator,
        bool $targetIsLocked,
    ): bool {
        if ($actorUserId !== $targetUserId) {
            return true;
        }

        return $targetIsSystemAdministrator && ! $targetIsLocked;
    }

    public function canUpdateMembership(
        string $actorUserId,
        string $targetUserId,
        bool $actorIsSystemAdministrator,
        bool $targetIsProjectAdministrator,
        string $targetStatus,
    ): bool {
        if ($actorUserId !== $targetUserId || $actorIsSystemAdministrator) {
            return true;
        }

        return $targetIsProjectAdministrator && $targetStatus === 'active';
    }

    public function canRemoveMembership(
        string $actorUserId,
        string $targetUserId,
        bool $targetIsProjectAdministrator,
    ): bool {
        return $actorUserId !== $targetUserId || ! $targetIsProjectAdministrator;
    }
}
