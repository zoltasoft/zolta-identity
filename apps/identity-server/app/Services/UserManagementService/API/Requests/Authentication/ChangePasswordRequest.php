<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use Illuminate\Support\Facades\Auth;
use Zolta\Http\Request\BaseRequest;

final class ChangePasswordRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function withData(): array
    {
        return [
            'user_id' => (string) $this->user()?->getAuthIdentifier(),
            'current_token_id' => $this->user()?->currentAccessToken()?->getKey(),
        ];
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'min:8'],
            'password' => ['required', 'string', 'min:8', 'different:current_password', 'confirmed'],
        ];
    }
}
