<?php

namespace App\Services\UserManagementService\API\Resources\Users;

use Zolta\Http\Response\Resources\Resource;

final class AccountProfileResource extends Resource
{
    public function toArray(): array
    {
        return [
            'profile' => $this->get('user')?->toArray(),
            'message' => 'Account profile updated successfully.',
        ];
    }
}
