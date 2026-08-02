<?php

namespace App\Services\UserManagementService\API\Resources\Permissions;

use Zolta\Http\Response\Resources\Resource;

final class PermissionResource extends Resource
{
    public function toArray(): array
    {
        return $this->all();
    }
}
