<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentitySessionReadOperation: string
{
    case Introspect = 'auth.introspect';
    case Current = 'auth.me';
    case Index = 'auth.sessions.index';
}
