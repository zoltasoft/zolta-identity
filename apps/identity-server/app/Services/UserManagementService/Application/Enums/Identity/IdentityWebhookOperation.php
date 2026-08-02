<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Enums\Identity;

enum IdentityWebhookOperation: string
{
    case Create = 'projects.webhooks.store';
    case Update = 'projects.webhooks.update';
    case RotateSecret = 'projects.webhooks.rotate';
    case Remove = 'projects.webhooks.destroy';
}
