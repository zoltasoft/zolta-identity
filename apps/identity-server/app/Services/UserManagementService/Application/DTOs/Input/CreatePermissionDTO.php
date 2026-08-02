<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class CreatePermissionDTO extends InputDTO
{
    /**
     * @param  string[]  $roleIds
     * @param  string[]  $userIds
     */
    public function __construct(
        #[FromRequest('name')]
        public readonly string $name,
        #[FromRequest('description')]
        public readonly ?string $description = null,
        #[FromRequest('role_ids')]
        public readonly array $roleIds = [],
        #[FromRequest('user_ids')]
        public readonly array $userIds = []
    ) {}
}
