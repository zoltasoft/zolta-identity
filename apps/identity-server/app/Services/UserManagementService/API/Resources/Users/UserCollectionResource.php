<?php

namespace App\Services\UserManagementService\API\Resources\Users;

use Zolta\Http\Response\Resources\Resource;

final class UserCollectionResource extends Resource
{
    public function toArray(): array
    {
        return [
            'users' => $this->get('users'),
            'meta' => $this->get('meta'),
            'captured' => $this->get('captured'),
        ];
    }
}
