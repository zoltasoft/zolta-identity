<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityHandoffOperation: string
{
    case Create = 'auth.handoff.create';
    case Exchange = 'auth.handoff.exchange';
}
