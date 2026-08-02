<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class AuthUserResource extends Resource
{
    public function toArray(): array
    {
        $user = $this->get('user');

        return [
            'response' => [
                'auth_user' => $user?->toArray(),
            ],
        ];
    }
}
