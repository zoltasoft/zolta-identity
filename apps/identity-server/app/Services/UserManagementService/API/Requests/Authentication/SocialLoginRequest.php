<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use Zolta\Http\Request\BaseRequest;

final class SocialLoginRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function routeParams(): array
    {
        return [
            'provider' => ['required', 'string'],
        ];
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|in:google',
            'access_token' => 'required|string',
        ];
    }
}
