<?php

namespace App\Services\UserManagementService\API\Requests\Roles;

use Zolta\Http\Request\BaseRequest;

class GetRoleByNameRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function routeParams(): array
    {
        return [
            'name' => [
                'type' => 'string',
                'required' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|exists:roles,role',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.exists' => 'No Role found with this role name.',
        ];
    }
}
