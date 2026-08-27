<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

class DeleteUserByEmailRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        return true;
    }

    /**
     * Define route parameters to merge.
     */
    public function routeParams(): array
    {
        return [
            'email' => [
                'type' => 'string',
                'required' => true,
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|exists:users,email',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email query parameter is required when not providing user URL parameter.',
            'email.exists' => 'The specified user does not exist.',
        ];
    }
}
