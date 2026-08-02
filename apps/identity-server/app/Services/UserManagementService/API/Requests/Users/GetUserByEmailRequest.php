<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

class GetUserByEmailRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function routeParams(): array
    {
        return [
            'email' => [
                'type' => 'string',
                'required' => true,
            ],
        ];
    }

    public function rules(): array
    {

        return [
            'email' => 'required|string|email|max:255|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'No user found with this email address.',
        ];
    }
}
