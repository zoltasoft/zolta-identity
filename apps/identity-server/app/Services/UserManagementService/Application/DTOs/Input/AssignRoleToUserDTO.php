<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class AssignRoleToUserDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('role')]
        public readonly string $roleId,
        #[FromRequest('user')]
        public readonly string $userId
    ) {}
}
