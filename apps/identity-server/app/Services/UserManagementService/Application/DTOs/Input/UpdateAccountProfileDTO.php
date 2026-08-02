<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class UpdateAccountProfileDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('user_id')]
        public readonly string $userId,
        #[FromRequest('username')]
        public readonly string $username,
        #[FromRequest('email')]
        public readonly string $email,
        #[FromRequest('avatar_url')]
        public readonly ?string $avatarUrl = null,
    ) {}
}
