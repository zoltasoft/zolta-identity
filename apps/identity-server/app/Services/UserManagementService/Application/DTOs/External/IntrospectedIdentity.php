<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\External;

final readonly class IntrospectedIdentity
{
    /** @param list<string> $roles @param list<string> $permissions */
    public function __construct(
        public string $userId,
        public string $projectId,
        public string $clientId,
        public ?string $sessionId,
        public string $email,
        public string $username,
        public array $roles,
        public array $permissions,
        public int $authorizationVersion,
        public int $expiresAt,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromIntrospection(array $payload): self
    {
        return new self(
            userId: (string) $payload['sub'],
            projectId: (string) $payload['project_id'],
            clientId: (string) $payload['client_id'],
            sessionId: isset($payload['session_id']) ? (string) $payload['session_id'] : null,
            email: (string) ($payload['email'] ?? ''),
            username: (string) ($payload['username'] ?? ''),
            roles: array_values($payload['roles'] ?? []),
            permissions: array_values($payload['permissions'] ?? []),
            authorizationVersion: (int) ($payload['authorization_version'] ?? 1),
            expiresAt: (int) $payload['exp'],
        );
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
