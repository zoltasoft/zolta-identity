<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class SocialLoginDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('provider')]
        public readonly string $provider,
        #[FromRequest('access_token')]
        public readonly string $accessToken,
    ) {}
}
