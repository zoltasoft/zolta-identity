<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use RuntimeException;
use Zolta\Exceptions\Contracts\RenderableExceptionInterface;

final class IdentityAccessDependencyException extends RuntimeException implements RenderableExceptionInterface
{
    /** @param array<string, mixed> $dependencies */
    public function __construct(
        private readonly string $resourceType,
        private readonly string $resourceId,
        private readonly array $dependencies,
    ) {
        parent::__construct(ucfirst($resourceType).' cannot be deleted while it is still in use.');
    }

    public function status(): int
    {
        return 409;
    }

    public function type(): string
    {
        return 'IdentityAccessDependencyException';
    }

    public function context(): array
    {
        return [
            'public' => [
                'code' => 'identity.access_dependency_conflict',
                'message' => $this->getMessage(),
                'resource_type' => $this->resourceType,
                'resource_id' => $this->resourceId,
                'dependencies' => $this->dependencies,
            ],
        ];
    }

    public function toErrorArray(): array
    {
        return ['type' => $this->type(), 'message' => $this->getMessage(), 'context' => $this->context()];
    }
}
