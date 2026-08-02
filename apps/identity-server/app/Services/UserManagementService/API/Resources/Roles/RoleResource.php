<?php

namespace App\Services\UserManagementService\API\Resources\Roles;

use Zolta\Http\Response\Resources\Resource;

final class RoleResource extends Resource
{
    public function toArray(): array
    {
        return [
            'role' => $this->get('role'),
        ];
    }
}
