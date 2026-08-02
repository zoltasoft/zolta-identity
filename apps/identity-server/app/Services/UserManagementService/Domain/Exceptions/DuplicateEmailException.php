<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

class DuplicateEmailException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'Duplicate email address!';
    }
}
