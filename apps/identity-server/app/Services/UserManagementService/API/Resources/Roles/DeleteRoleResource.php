<?php

namespace App\Services\UserManagementService\API\Resources\Roles;

use Zolta\Http\Response\Resources\Resource;

final class DeleteRoleResource extends Resource
{
    public function toArray(): array
    {
        return [
            'deleted' => true,
            'role_id' => $this->get('roleId'),
            'message' => $this->get('message'),
        ];
    }
}
