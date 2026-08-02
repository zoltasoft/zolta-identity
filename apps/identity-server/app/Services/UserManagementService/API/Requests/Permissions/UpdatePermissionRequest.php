<?php

namespace App\Services\UserManagementService\API\Requests\Permissions;

use Zolta\Http\Request\BaseRequest;

final class UpdatePermissionRequest extends BaseRequest
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
            'id' => 'required|string|exists:permissions,id',
            'name' => [
                'sometimes',
                'string',
                'max:255',
                'unique:permissions,name',
            ],
            'description' => 'nullable|string|max:255',
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'string|exists:roles,id',
            'user_ids' => 'sometimes|array',
            'user_ids.*' => 'string|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The permission id is required.',
            'id.exists' => 'The specified permission does not exist.',
            'name.unique' => 'This permission name already exists.',
            'role_ids.*.exists' => 'One or more roles do not exist.',
            'user_ids.*.exists' => 'One or more users do not exist.',
        ];
    }
}
