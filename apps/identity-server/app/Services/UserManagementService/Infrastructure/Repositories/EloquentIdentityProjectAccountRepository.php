<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Repositories\IdentityProjectAccountRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectAccount;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Domain\ValueObjects\Username;

final class EloquentIdentityProjectAccountRepository implements IdentityProjectAccountRepository
{
    public function updateProfile(
        IdentityProjectId $projectId,
        UserId $userId,
        Username $username,
        ?string $profilePicture,
    ): void {
        $account = IdentityProjectAccount::query()
            ->where('project_id', $projectId->toString())
            ->where('user_id', $userId->get('value'))
            ->firstOrFail();

        $account->forceFill([
            'username' => $username->get('username'),
            'profile_picture' => $profilePicture,
        ])->save();
    }
}
