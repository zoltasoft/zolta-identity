<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use RuntimeException;
use Zolta\Exceptions\Contracts\RenderableExceptionInterface;

final class IdentityAuthenticationException extends RuntimeException implements RenderableExceptionInterface
{
    public function status(): int
    {
        return 401;
    }

    public function type(): string
    {
        return 'IdentityAuthenticationException';
    }

    public function context(): array
    {
        return ['public' => ['code' => 'identity.unauthenticated', 'message' => $this->getMessage()]];
    }

    public function toErrorArray(): array
    {
        return ['type' => $this->type(), 'message' => $this->getMessage(), 'context' => $this->context()];
    }
}
