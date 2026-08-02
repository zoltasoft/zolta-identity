<?php

namespace App\Services\UserManagementService\API\Requests\Roles;

use Zolta\Http\Request\BaseRequest;

final class UpdateRoleRequest extends BaseRequest
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
            'id' => 'required|string|exists:roles,id',
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'description' => 'nullable|string|max:255',
            'permission_ids' => 'sometimes|array',
            'permission_ids.*' => 'string|exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The specified role does not exist.',
            'name.unique' => 'This role name already exists.',
            'permission_ids.*.exists' => 'One or more permissions do not exist.',
        ];
    }
}
