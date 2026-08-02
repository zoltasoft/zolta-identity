<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class TemporaryAccountResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $expires_at,
    ) {}
}
