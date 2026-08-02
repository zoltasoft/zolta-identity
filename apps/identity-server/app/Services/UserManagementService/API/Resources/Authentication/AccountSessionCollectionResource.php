<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class AccountSessionCollectionResource extends Resource
{
    public function toArray(): array
    {
        return ['sessions' => $this->get('sessions')];
    }
}
