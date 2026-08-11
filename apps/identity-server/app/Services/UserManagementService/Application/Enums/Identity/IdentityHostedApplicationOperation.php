<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityHostedApplicationOperation: string
{
    case Create = 'projects.hosted_applications.store';
    case Update = 'projects.hosted_applications.update';
    case Delete = 'projects.hosted_applications.destroy';
}
