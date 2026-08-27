<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use RuntimeException;
use Zolta\Exceptions\Contracts\RenderableExceptionInterface;

final class IdentityProjectLifecycleException extends RuntimeException implements RenderableExceptionInterface
{
    public function status(): int
    {
        return 409;
    }

    public function type(): string
    {
        return 'IdentityProjectLifecycleException';
    }

    public function context(): array
    {
        return ['public' => ['code' => 'identity.project_lifecycle_conflict', 'message' => $this->getMessage()]];
    }

    public function toErrorArray(): array
    {
        return ['type' => $this->type(), 'message' => $this->getMessage(), 'context' => $this->context()];
    }
}
