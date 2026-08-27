<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityProjectAccessOperation: string
{
    case CreateRole = 'projects.roles.store';
    case DeleteRole = 'projects.roles.destroy';
    case CreatePermission = 'projects.permissions.store';
    case DeletePermission = 'projects.permissions.destroy';
    case SetRolePermissions = 'projects.roles.permissions';
    case Invite = 'projects.invitations.store';
    case SetMembershipAccess = 'projects.memberships.access';
    case RemoveMembership = 'projects.memberships.destroy';
}
