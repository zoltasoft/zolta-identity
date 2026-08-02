<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\AcceptIdentityInvitation;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\AcceptIdentityInvitation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(AcceptIdentityInvitationCommand::class)]
final readonly class AcceptIdentityInvitationCommandHandler
{
    public function __construct(private AcceptIdentityInvitation $invitations) {}

    public function __invoke(AcceptIdentityInvitationCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload(
            $this->invitations->acceptInvitation($command->input),
        ));
    }
}
