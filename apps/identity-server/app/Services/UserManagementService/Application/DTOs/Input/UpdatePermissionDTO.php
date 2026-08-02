<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class UpdatePermissionDTO extends InputDTO
{
    /**
     * @param  string[]|null  $roleIds
     * @param  string[]|null  $userIds
     */
    public function __construct(
        #[FromRequest('id')]
        public readonly string $permissionId,
        #[FromRequest('name')]
        public readonly ?string $name = null,
        #[FromRequest('description')]
        public readonly ?string $description = null,
        #[FromRequest('role_ids')]
        public readonly ?array $roleIds = null,
        #[FromRequest('user_ids')]
        public readonly ?array $userIds = null
    ) {}
}
