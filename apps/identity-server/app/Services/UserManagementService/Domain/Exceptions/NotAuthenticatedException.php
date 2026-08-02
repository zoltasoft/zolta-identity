<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class NotAuthenticatedException extends UnauthorizedHttpException
{
    public function __construct()
    {
        parent::__construct('Bearer', 'Unauthenticated user.');
    }
}
