<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class RevokeAccountSessionDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('user_id')]
        public readonly string $userId,
        #[FromRequest('session')]
        public readonly int $sessionId,
    ) {}
}
