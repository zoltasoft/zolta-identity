<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Enums;

enum IdentityProjectMode: string
{
    case Live = 'live';
    case Sandbox = 'sandbox';
}
