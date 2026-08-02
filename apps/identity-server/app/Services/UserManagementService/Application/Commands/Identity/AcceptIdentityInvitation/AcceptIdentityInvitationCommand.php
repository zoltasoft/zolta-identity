<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\AcceptIdentityInvitation;

use Zolta\Cqrs\Commands\Command;

final class AcceptIdentityInvitationCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(public readonly array $input) {}
}
