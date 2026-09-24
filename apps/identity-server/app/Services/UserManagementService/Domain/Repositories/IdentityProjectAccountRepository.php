<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Domain\ValueObjects\Username;

interface IdentityProjectAccountRepository
{
    public function updateProfile(
        IdentityProjectId $projectId,
        UserId $userId,
        Username $username,
        ?string $profilePicture,
    ): void;
}
