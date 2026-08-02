<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

class UnauthorizedActionException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'Unauthorized action attempted.';
    }
}
