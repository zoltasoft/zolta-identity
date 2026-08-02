<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use Zolta\Http\Request\BaseRequest;

final class CompletePasswordResetRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
