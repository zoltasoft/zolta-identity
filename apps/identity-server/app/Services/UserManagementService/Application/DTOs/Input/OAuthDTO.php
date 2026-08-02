<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

class OAuthDTO
{
    /**
     * @param  string  $socialProvider  Requested Social Provider
     */
    public function __construct(
        public string $socialProvider,
    ) {}
}
