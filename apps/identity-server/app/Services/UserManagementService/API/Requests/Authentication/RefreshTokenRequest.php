<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use Zolta\Http\Request\BaseRequest;

final class RefreshTokenRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user() ? true : false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => (string) $this->user()?->getAuthIdentifier(),
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|string|uuid',
        ];
    }
}
