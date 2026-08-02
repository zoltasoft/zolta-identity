<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

class DefaultRoleNotFoundException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'The default role "User" was not found!';
    }
}
