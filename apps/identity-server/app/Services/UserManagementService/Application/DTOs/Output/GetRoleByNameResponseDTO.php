<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class GetRoleByNameResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $role,
    ) {}
}
