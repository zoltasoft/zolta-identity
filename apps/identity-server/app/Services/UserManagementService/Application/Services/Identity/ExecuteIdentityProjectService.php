<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Identity;

use App\Services\UserManagementService\Application\Commands\Identity\ConfigureIdentityProjectEnvironment\ConfigureIdentityProjectEnvironmentCommand;
use App\Services\UserManagementService\Application\Commands\Identity\ConfigureIdentityProjectRegistration\ConfigureIdentityProjectRegistrationCommand;
use App\Services\UserManagementService\Application\Commands\Identity\CreateIdentityProject\CreateIdentityProjectCommand;
use App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityClient\ExecuteIdentityClientCommand;
use App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityProjectAccess\ExecuteIdentityProjectAccessCommand;
use App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityWebhook\ExecuteIdentityWebhookCommand;
use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityClientOperation;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityProjectAccessOperation;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityWebhookOperation;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectRegistrationMode;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use InvalidArgumentException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ExecuteIdentityProjectService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(IdentityOperationDTO $dto): mixed
    {
        $actorUserId = $dto->actorUserId
            ?? throw new InvalidArgumentException('An authenticated Identity actor is required.');

        if ($dto->operation === 'projects.store') {
            return $this->execute(CreateIdentityProjectCommand::class, [
                'actorUserId' => $actorUserId,
                'name' => (string) $dto->input['name'],
                'slug' => (string) $dto->input['slug'],
                'description' => isset($dto->input['description'])
                    ? (string) $dto->input['description']
                    : null,
            ]);
        }

        if ($dto->operation === 'projects.registration.update') {
            return $this->execute(ConfigureIdentityProjectRegistrationCommand::class, [
                'actorUserId' => $actorUserId,
                'projectId' => IdentityProjectId::fromString((string) $dto->input['project']),
                'mode' => IdentityProjectRegistrationMode::from(
                    (string) $dto->input['registration_mode'],
                ),
                'roleId' => isset($dto->input['registration_role_id'])
                    ? (string) $dto->input['registration_role_id']
                    : null,
            ]);
        }

        if ($dto->operation === 'projects.environment.update') {
            return $this->execute(ConfigureIdentityProjectEnvironmentCommand::class, [
                'actorUserId' => $actorUserId,
                'projectId' => IdentityProjectId::fromString((string) $dto->input['project']),
                'mode' => IdentityProjectMode::from((string) $dto->input['mode']),
                'sandboxTtlMinutes' => (int) $dto->input['sandbox_ttl_minutes'],
            ]);
        }

        $family = match (true) {
            IdentityWebhookOperation::tryFrom($dto->operation) !== null => [
                ExecuteIdentityWebhookCommand::class,
                IdentityWebhookOperation::from($dto->operation),
            ],
            IdentityClientOperation::tryFrom($dto->operation) !== null => [
                ExecuteIdentityClientCommand::class,
                IdentityClientOperation::from($dto->operation),
            ],
            IdentityProjectAccessOperation::tryFrom($dto->operation) !== null => [
                ExecuteIdentityProjectAccessCommand::class,
                IdentityProjectAccessOperation::from($dto->operation),
            ],
            default => throw new InvalidArgumentException(
                "Unsupported Identity project operation [{$dto->operation}].",
            ),
        };

        return $this->execute($family[0], [
            'operation' => $family[1],
            'input' => $dto->input,
            'actorUserId' => $actorUserId,
            'projectId' => (string) $dto->input['project'],
        ]);
    }

    /** @param class-string $command @param array<string, mixed> $arguments */
    private function execute(string $command, array $arguments): mixed
    {
        ['result' => $result] = $this->applicationService
            ->runAndCapture($command, $arguments)
            ->getOrFail();

        return $result;
    }
}
