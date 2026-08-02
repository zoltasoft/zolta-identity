<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class IdentityOperationDTO extends InputDTO
{
    /** @param array<string, mixed> $input */
    public function __construct(
        #[FromRequest('operation')]
        public readonly string $operation,
        #[FromRequest('input')]
        public readonly array $input,
        #[FromRequest('actor_user_id')]
        public readonly ?string $actorUserId = null,
        #[FromRequest('access_token')]
        public readonly ?string $accessToken = null,
        #[FromRequest('ip_address')]
        public readonly ?string $ipAddress = null,
        #[FromRequest('user_agent')]
        public readonly ?string $userAgent = null,
    ) {}
}
