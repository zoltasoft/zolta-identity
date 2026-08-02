<?php

namespace App\Services\UserManagementService\API\Requests\Roles;

use Zolta\Http\Request\BaseRequest;

final class RevokeRoleFromUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function routeParams(): array
    {
        return [
            'role' => [
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
            'role' => 'required|string|exists:roles,id',
            'user' => 'required|string|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'role.exists' => 'The specified role does not exist.',
            'user.exists' => 'The specified user does not exist.',
        ];
    }
}
