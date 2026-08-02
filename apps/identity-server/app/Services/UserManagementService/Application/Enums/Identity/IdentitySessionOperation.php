<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentitySessionOperation: string
{
    case Refresh = 'auth.refresh';
    case Logout = 'auth.logout';
    case Revoke = 'auth.sessions.revoke';
}
