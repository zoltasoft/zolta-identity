<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Exceptions;

use RuntimeException;

final class IdentityAuthorizationException extends RuntimeException
{
    public function render(): mixed
    {
        return response()->json(['message' => $this->getMessage()], 403);
    }
}
