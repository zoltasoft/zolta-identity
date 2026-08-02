<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityClientOperation: string
{
    case Create = 'projects.clients.store';
    case RotateSecret = 'projects.clients.rotate';
    case SetStatus = 'projects.clients.status';
    case SyncPermissionManifest = 'projects.clients.manifest';
}
