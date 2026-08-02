<?php

namespace App\Services\UserManagementService\API\Resources\Users;

use Zolta\Http\Response\Resources\Resource;

final class ProvisionedUserAccessResource extends Resource
{
    public function toArray(): array
    {
        return [
            'provisioning' => $this->get('payload'),
        ];
    }
}
