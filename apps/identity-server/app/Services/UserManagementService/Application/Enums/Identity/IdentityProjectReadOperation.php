<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityProjectReadOperation: string
{
    case Index = 'projects.index';
    case Show = 'projects.show';
    case Audit = 'projects.audit';
}
