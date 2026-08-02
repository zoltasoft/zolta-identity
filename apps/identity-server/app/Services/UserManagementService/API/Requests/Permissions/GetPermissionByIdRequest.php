<?php

namespace App\Services\UserManagementService\API\Requests\Permissions;

use Zolta\Http\Request\BaseRequest;

final class GetPermissionByIdRequest extends BaseRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'The specified permission does not exist.',
        ];
    }
}
