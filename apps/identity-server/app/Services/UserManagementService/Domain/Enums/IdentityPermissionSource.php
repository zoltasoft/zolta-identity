<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Enums;

enum IdentityPermissionSource: string
{
    case Manual = 'manual';
    case Manifest = 'manifest';
    case Catalog = 'catalog';
}
