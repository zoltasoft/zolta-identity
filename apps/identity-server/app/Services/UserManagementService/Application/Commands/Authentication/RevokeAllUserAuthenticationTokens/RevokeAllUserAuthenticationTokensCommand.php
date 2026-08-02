<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RevokeAllUserAuthenticationTokens;

use Zolta\Cqrs\Commands\Command;

class RevokeAllUserAuthenticationTokensCommand extends Command
{
    public function __construct() {}
}
