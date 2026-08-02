<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

class GetRoleByNameDTO extends InputDTO
{
    final public function __construct(
        public readonly string $name,
    ) {}
}
