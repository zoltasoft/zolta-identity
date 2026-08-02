<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class UpdateRoleDTO extends InputDTO
{
    /**
     * @param  string[]|null  $permissionIds
     */
    public function __construct(
        #[FromRequest('id')]
        public readonly string $roleId,
        #[FromRequest('name')]
        public readonly ?string $name = null,
        #[FromRequest('description')]
        public readonly ?string $description = null,
        #[FromRequest('permission_ids')]
        public readonly ?array $permissionIds = null
    ) {}
}
