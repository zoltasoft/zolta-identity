<?php

namespace App\Services\UserManagementService\API\Resources\Roles;

use Zolta\Http\Response\Resources\Resource;

final class RoleCollectionResource extends Resource
{
    public function toArray(): array
    {
        return [
            'roles' => $this->get('roles'),
        ];
    }
}
