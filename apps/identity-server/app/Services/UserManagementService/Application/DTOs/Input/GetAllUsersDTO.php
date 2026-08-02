<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

class GetAllUsersDTO extends InputDTO
{
    final public function __construct(
        public readonly int $limit,
        public readonly int $page,
        public readonly string $include,
        public readonly string $filters,
    ) {}
}
