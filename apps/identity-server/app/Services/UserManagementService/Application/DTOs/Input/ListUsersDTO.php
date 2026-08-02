<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\DTO\Input\InputDTO;

final class ListUsersDTO extends InputDTO
{
    /**
     * @param  array<string,mixed>  $options
     */
    public function __construct(public readonly array $options = []) {}
}
