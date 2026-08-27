<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\SecretGenerator;
use Illuminate\Support\Str;

final class LaravelSecretGenerator implements SecretGenerator
{
    public function generate(int $length): string
    {
        return Str::random($length);
    }
}
