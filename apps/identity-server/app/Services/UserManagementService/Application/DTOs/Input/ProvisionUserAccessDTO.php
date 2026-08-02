<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class ProvisionUserAccessDTO extends InputDTO
{
    /**
     * @param  array<int,string>  $permissionIds
     */
    public function __construct(
        #[FromRequest('user_id')]
        public readonly string $userId,
        #[FromRequest('role_id')]
        public readonly string $roleId,
        #[FromRequest('permission_ids')]
        public readonly array $permissionIds = [],
        #[FromRequest('attach_permissions_to_role')]
        public readonly bool $attachPermissionsToRole = true,
    ) {}
}
