<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

class DeleteUserByIdRequest extends BaseRequest
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
            'id' => [
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
            'id' => 'required|string|exists:users,id',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'id.required' => 'The ID parameter is required.',
            'id.exists' => 'The specified user does not exist.',
        ];
    }
}
