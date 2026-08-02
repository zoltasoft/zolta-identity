<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use Illuminate\Support\Facades\Auth;
use Zolta\Http\Request\BaseRequest;

final class VerifyEmailRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function trustedData(): array
    {
        return ['user_id' => (string) $this->user()?->getAuthIdentifier()];
    }

    public function rules(): array
    {
        return ['code' => ['required', 'digits:6']];
    }
}
