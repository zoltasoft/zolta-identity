<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityProjectCatalogOperation: string
{
    case Index = 'project_access_catalog.index';
    case CreatePermission = 'project_access_catalog.permissions.store';
    case CreateRole = 'project_access_catalog.roles.store';
    case Import = 'projects.access_catalog.import';
    case PublishPermission = 'projects.permissions.publish';
    case PublishRole = 'projects.roles.publish';
}
