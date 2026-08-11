<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityHostedApplication;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityHostedApplications;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityHostedApplicationOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityHostedApplicationCommand::class)]
final readonly class ExecuteIdentityHostedApplicationCommandHandler
{
    public function __construct(private ManageIdentityHostedApplications $applications) {}

    public function __invoke(ExecuteIdentityHostedApplicationCommand $command): Result
    {
        $result = match ($command->operation) {
            IdentityHostedApplicationOperation::Create => $this->applications->createHostedApplication(
                $command->actorUserId,
                $command->projectId,
                $command->input,
            ),
            IdentityHostedApplicationOperation::Update => $this->update($command),
            IdentityHostedApplicationOperation::Delete => $this->delete($command),
        };

        return Result::success(new IdentityOperationPayload($result));
    }

    /** @return array{message: string} */
    private function update(ExecuteIdentityHostedApplicationCommand $command): array
    {
        $this->applications->updateHostedApplication(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['hosted_application'],
            $command->input,
        );

        return ['message' => 'Hosted application updated.'];
    }

    /** @return array{message: string} */
    private function delete(ExecuteIdentityHostedApplicationCommand $command): array
    {
        $this->applications->removeHostedApplication(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['hosted_application'],
        );

        return ['message' => 'Hosted application removed.'];
    }
}
