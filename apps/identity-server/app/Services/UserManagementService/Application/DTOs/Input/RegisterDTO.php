<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use App\Services\UserManagementService\Application\Attributes\Validation\Accepted;
use App\Services\UserManagementService\Application\Attributes\Validation\Email;
use App\Services\UserManagementService\Application\Attributes\Validation\MaxLength;
use App\Services\UserManagementService\Application\Attributes\Validation\Required;
use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

class RegisterDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('username')]
        #[Required]
        #[MaxLength(255)]
        public string $username,

        #[FromRequest('email')]
        #[Required]
        #[Email]
        #[MaxLength(255)]
        public string $email,

        #[FromRequest('password')]
        #[Required]
        #[MaxLength(255)]
        public string $password,

        #[FromRequest('terms')]
        #[Required]
        #[Accepted]
        public bool $terms,
    ) {}
}
