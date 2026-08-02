<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin;

use Zolta\Cqrs\Commands\Command;

final class EnsureOAuthProviderCommand extends Command
{
    public function __construct(public readonly string $provider) {}
}
