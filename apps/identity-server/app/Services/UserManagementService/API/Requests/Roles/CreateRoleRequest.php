<?php

namespace App\Services\UserManagementService\API\Requests\Roles;

use Zolta\Http\Request\BaseRequest;

final class CreateRoleRequest extends BaseRequest
{
    public function authorize(): bool
    {

        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,role',
            'description' => 'nullable|string|max:255',
            'permission_ids' => 'sometimes|array',
            'permission_ids.*' => 'string|exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This role name already exists.',
            'permission_ids.*.exists' => 'One or more permissions do not exist.',
        ];
    }
}
