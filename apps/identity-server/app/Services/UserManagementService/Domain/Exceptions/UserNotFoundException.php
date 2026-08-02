<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

class UserNotFoundException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'The User not found!.';
    }
}
