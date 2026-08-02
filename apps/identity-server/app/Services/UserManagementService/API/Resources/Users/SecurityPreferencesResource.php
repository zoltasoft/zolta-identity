<?php

namespace App\Services\UserManagementService\API\Resources\Users;

use Zolta\Http\Response\Resources\Resource;

final class SecurityPreferencesResource extends Resource
{
    public function toArray(): array
    {
        return [
            'security' => $this->all(),
            'message' => 'Security preferences updated successfully.',
        ];
    }
}
