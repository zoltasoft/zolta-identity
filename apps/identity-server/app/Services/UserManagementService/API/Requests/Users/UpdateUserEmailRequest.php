<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

class UpdateUserEmailRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function routeParams(): array
    {
        return [
            'id' => [
                'type' => 'string',
                'required' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'exists:users,id'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'User ID is required.',
            'id.exists' => 'User not found.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'This email is already taken.',
        ];
    }
}
