<?php

namespace App\Services\UserManagementService\API\Requests\Permissions;

use Zolta\Http\Request\BaseRequest;

final class AssignPermissionToUserRequest extends BaseRequest
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
            'user' => [
                'type' => 'string',
                'required' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'permission' => 'required|string|exists:permissions,id',
            'user' => 'required|string|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'permission.exists' => 'The specified permission does not exist.',
            'user.exists' => 'The specified user does not exist.',
        ];
    }
}
