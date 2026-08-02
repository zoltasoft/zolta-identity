<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use Zolta\Http\Request\BaseRequest;

final class LogoutRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user() ? true : false;
    }

    public function rules(): array
    {
        return [
            'token_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
