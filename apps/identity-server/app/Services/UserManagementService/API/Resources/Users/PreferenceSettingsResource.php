<?php

namespace App\Services\UserManagementService\API\Resources\Users;

use Zolta\Http\Response\Resources\Resource;

final class PreferenceSettingsResource extends Resource
{
    public function toArray(): array
    {
        return [
            'preferences' => $this->all(),
            'message' => 'Preferences updated successfully.',
        ];
    }
}
