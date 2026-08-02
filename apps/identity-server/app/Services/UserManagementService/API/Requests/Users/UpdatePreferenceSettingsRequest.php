<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

final class UpdatePreferenceSettingsRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function trustedData(): array
    {
        return [
            'user_id' => (string) $this->user()?->getAuthIdentifier(),
        ];
    }

    public function rules(): array
    {
        return [
            'theme' => 'required|string|in:light,dark,system',
            'language' => 'required|string|max:10',
        ];
    }
}
