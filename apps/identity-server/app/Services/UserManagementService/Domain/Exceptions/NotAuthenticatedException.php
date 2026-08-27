<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Exceptions;

use RuntimeException;

final class NotAuthenticatedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unauthenticated user.');
    }
}
