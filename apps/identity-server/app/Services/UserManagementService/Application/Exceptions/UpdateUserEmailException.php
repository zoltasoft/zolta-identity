<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use Zolta\Domain\Exceptions\BaseException;

final class UpdateUserEmailException extends BaseException
{
    protected function exceptionMessage(): string
    {
        return 'Operation failed while updating user email.';
    }
}
