<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityPasswordRecoveryOperation: string
{
    case Forgot = 'auth.password.forgot';
    case Reset = 'auth.password.reset';
}
