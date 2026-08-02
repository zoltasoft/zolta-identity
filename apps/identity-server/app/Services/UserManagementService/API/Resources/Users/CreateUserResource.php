<?php

namespace App\Services\UserManagementService\API\Resources\Users;

use Zolta\Http\Response\Resources\Resource;

final class CreateUserResource extends Resource
{
    public function toArray(): array
    {
        return $this->all();
    }
}
