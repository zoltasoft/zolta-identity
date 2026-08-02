<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Enums;

enum IdentityProjectRegistrationMode: string
{
    case InviteOnly = 'invite_only';
    case Public = 'public';
}
