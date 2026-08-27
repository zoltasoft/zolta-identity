<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Enums;

enum IdentityProjectStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case PendingDeletion = 'pending_deletion';
}
