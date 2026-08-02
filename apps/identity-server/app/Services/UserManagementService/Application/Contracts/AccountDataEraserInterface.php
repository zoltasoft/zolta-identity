<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

use Zolta\Domain\ValueObjects\UserId;

interface AccountDataEraserInterface
{
    public function erase(UserId $userId, string $email): void;
}
