<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

class UpdateUserEmailDTO extends InputDTO
{
    final public function __construct(
        public readonly string $id,
        public readonly string $email,
    ) {}
}
