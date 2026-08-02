<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use App\Services\UserManagementService\Application\Attributes\Validation\Required;
use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class ChangePasswordDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('user_id')]
        #[Required]
        public readonly string $userId,
        #[FromRequest('current_password')]
        #[Required]
        public readonly string $currentPassword,
        #[FromRequest('password')]
        #[Required]
        public readonly string $password,
        #[FromRequest('current_token_id')]
        public readonly ?int $currentTokenId = null,
    ) {}
}
