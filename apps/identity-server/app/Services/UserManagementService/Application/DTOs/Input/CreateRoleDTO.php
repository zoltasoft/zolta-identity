<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class CreateRoleDTO extends InputDTO
{
    /**
     * @param  string[]  $permissionIds
     */
    public function __construct(
        #[FromRequest('name')]
        public readonly string $name,
        #[FromRequest('description')]
        public readonly ?string $description = null,
        #[FromRequest('permission_ids')]
        public readonly array $permissionIds = []
    ) {}
}
