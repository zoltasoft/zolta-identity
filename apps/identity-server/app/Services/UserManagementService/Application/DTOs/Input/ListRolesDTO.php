<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

final class ListRolesDTO extends InputDTO
{
    public function __construct(public readonly array $options = []) {}
}
