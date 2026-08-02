<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

class CreateUserDTO extends InputDTO
{
    final public function __construct(
        public readonly string $email,
        public readonly string $username,
        public readonly string $password,
        public readonly ?bool $terms = null,
    ) {}
}
