<?php

namespace App\Services\UserManagementService\API\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

class ActionNotAllowedException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'Action Not Allowed!';
    }
}
