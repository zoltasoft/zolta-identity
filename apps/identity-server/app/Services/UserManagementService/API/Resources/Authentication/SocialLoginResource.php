<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class SocialLoginResource extends Resource
{
    public function toArray(): array
    {
        return [
            'access_token' => $this->get('accessToken'),
            'token_type' => 'Bearer',
            'user' => $this->get('user')?->toArray(),
        ];
    }
}
