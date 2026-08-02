<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

class InvalidPasswordException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'Invalid password provided.';
    }
}
