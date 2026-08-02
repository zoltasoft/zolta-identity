<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

class LoginFailedException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'Cannot login the user!';
    }
}
