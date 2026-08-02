<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use Illuminate\Support\Facades\Auth;
use Zolta\Http\Request\BaseRequest;

final class RevokeAccountSessionRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function trustedData(): array
    {
        return ['user_id' => (string) $this->user()?->getAuthIdentifier()];
    }

    public function routeParams(): array
    {
        return [
            'session' => [
                'type' => 'integer',
                'required' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return ['session' => ['required', 'integer', 'min:1']];
    }
}
