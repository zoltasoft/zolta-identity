<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Enums;

enum IdentityWebhookStatus: string
{
    case Active = 'active';
    case Disabled = 'disabled';
}
