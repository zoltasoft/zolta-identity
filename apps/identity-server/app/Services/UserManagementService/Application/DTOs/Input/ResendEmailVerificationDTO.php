<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use App\Services\UserManagementService\Application\Attributes\Validation\Required;
use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class ResendEmailVerificationDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('user_id')]
        #[Required]
        public readonly string $userId,
    ) {}
}
