<?php

namespace App\Services\UserManagementService\API\Requests\Permissions;

use Zolta\Http\Request\BaseRequest;

final class AssignPermissionToRoleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function routeParams(): array
    {
        return [
            'permission' => [
                'type' => 'string',
                'required' => true,
            ],
            'role' => [
                'type' => 'string',
                'required' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'permission' => 'required|string|exists:permissions,id',
            'role' => 'required|string|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'permission.exists' => 'The specified permission does not exist.',
            'role.exists' => 'The specified role does not exist.',
        ];
    }
}
