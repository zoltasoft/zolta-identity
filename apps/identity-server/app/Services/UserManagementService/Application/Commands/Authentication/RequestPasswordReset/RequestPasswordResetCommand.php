<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RequestPasswordReset;

use Zolta\Cqrs\Commands\Command;

final class RequestPasswordResetCommand extends Command
{
    public function __construct(public readonly string $email) {}
}
