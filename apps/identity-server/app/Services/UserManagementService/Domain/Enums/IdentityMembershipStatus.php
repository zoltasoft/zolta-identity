<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Enums;

enum IdentityMembershipStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
