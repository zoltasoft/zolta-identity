<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityWebhook;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityWebhooks;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityWebhookOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityWebhookCommand::class)]
final readonly class ExecuteIdentityWebhookCommandHandler
{
    public function __construct(private ManageIdentityWebhooks $webhooks) {}

    public function __invoke(ExecuteIdentityWebhookCommand $command): Result
    {
        $result = match ($command->operation) {
            IdentityWebhookOperation::Create => $this->webhooks->createWebhook(
                $command->actorUserId,
                $command->projectId,
                (string) $command->input['url'],
                (array) $command->input['events'],
            ),
            IdentityWebhookOperation::Update => $this->update($command),
            IdentityWebhookOperation::RotateSecret => $this->webhooks->rotateWebhookSecret(
                $command->actorUserId,
                $command->projectId,
                (string) $command->input['webhook'],
            ),
            IdentityWebhookOperation::Remove => $this->remove($command),
        };

        return Result::success(new IdentityOperationPayload($result));
    }

    /** @return array{message: string} */
    private function update(ExecuteIdentityWebhookCommand $command): array
    {
        $this->webhooks->updateWebhook(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['webhook'],
            (string) $command->input['url'],
            (array) $command->input['events'],
            (string) $command->input['status'],
        );

        return ['message' => 'Webhook updated.'];
    }

    /** @return array{message: string} */
    private function remove(ExecuteIdentityWebhookCommand $command): array
    {
        $this->webhooks->removeWebhook(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['webhook'],
        );

        return ['message' => 'Webhook removed.'];
    }
}
