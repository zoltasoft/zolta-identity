<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityAccessOperation: string
{
    case Login = 'auth.login';
    case Register = 'auth.register';
    case SocialLogin = 'auth.social';
    case SandboxSession = 'auth.sandbox-session';
}
