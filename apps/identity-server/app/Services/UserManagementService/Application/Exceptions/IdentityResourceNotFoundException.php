<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use RuntimeException;
use Zolta\Exceptions\Contracts\RenderableExceptionInterface;

final class IdentityResourceNotFoundException extends RuntimeException implements RenderableExceptionInterface
{
    public function __construct(string $resource = 'Identity resource')
    {
        parent::__construct("{$resource} was not found.");
    }

    public function status(): int
    {
        return 404;
    }

    public function type(): string
    {
        return 'IdentityResourceNotFoundException';
    }

    public function context(): array
    {
        return ['public' => ['code' => 'identity.not_found', 'message' => $this->getMessage()]];
    }

    public function toErrorArray(): array
    {
        return ['type' => $this->type(), 'message' => $this->getMessage(), 'context' => $this->context()];
    }
}
