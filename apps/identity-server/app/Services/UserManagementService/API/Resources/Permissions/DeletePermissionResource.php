<?php

namespace App\Services\UserManagementService\API\Resources\Permissions;

use Zolta\Http\Response\Resources\Resource;

final class DeletePermissionResource extends Resource
{
    public function toArray(): array
    {
        return [
            'deleted' => true,
            'permission_id' => $this->get('permissionId'),
            'message' => $this->get('message'),
        ];
    }
}
