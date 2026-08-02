<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use Zolta\Exceptions\Rest\ConflictException;

final class EmailAlreadyInUseException extends ConflictException
{
    protected function exceptionMessage(): string
    {
        return 'The email has already been taken.';
    }
}
