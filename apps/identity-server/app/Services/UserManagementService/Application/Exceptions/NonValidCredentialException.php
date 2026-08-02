<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Exceptions;

use Zolta\Exceptions\Rest\BadRequestException;

class NonValidCredentialException extends BadRequestException
{
    protected function exceptionMessage(): string
    {
        return 'Credentials not valid!';
    }
}
