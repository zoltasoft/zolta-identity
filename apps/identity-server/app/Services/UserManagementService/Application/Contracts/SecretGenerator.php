<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

interface SecretGenerator
{
    public function generate(int $length): string;
}
