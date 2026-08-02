<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class LogoutResource extends Resource
{
    public function toArray(): array
    {
        return [
            'message' => $this->get('message'),
        ];
    }
}
