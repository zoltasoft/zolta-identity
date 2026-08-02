<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class RefreshResource extends Resource
{
    public function toArray(): array
    {
        $user = $this->get('user');

        return [
            'access_token' => $this->get('accessToken'),
            'token_type' => 'Bearer',
            'user' => $user?->toArray(),
        ];
    }
}
