<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use App\Services\UserManagementService\Application\Attributes\Validation\Email;
use App\Services\UserManagementService\Application\Attributes\Validation\Required;
use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class RequestPasswordResetDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('email')]
        #[Required]
        #[Email]
        public readonly string $email,
    ) {}
}
