<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use RuntimeException;
use Zolta\Exceptions\Contracts\RenderableExceptionInterface;

final class IdentityAuthorizationException extends RuntimeException implements RenderableExceptionInterface
{
    public function status(): int
    {
        return 403;
    }

    public function type(): string
    {
        return 'IdentityAuthorizationException';
    }

    public function context(): array
    {
        return ['public' => ['code' => 'identity.forbidden', 'message' => $this->getMessage()]];
    }

    public function toErrorArray(): array
    {
        return ['type' => $this->type(), 'message' => $this->getMessage(), 'context' => $this->context()];
    }
}
