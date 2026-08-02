<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

final class UpdateAccountProfileRequest extends BaseRequest
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
            'username' => 'required|string|min:3|max:100',
            'email' => 'required|email',
            'avatar_url' => 'nullable|url',
        ];
    }
}
