<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Enums;

enum IdentityPermissionStatus: string
{
    case Active = 'active';
    case Stale = 'stale';
}
