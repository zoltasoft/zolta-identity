<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

class GetUserByIdRequest extends BaseRequest
{
    public function authorize(): bool
    {
        // $this->authorizeAction(['admin.access']);
        return true;
    }

    public function queryOptions(): array
    {
        return [
            'default_include' => [],
            'strict' => true,
        ];
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

    public function queryParams(): array
    {
        return [
            'include' => ['type' => 'array', 'required' => false],
        ];
    }

    public function rules(): array
    {
        return [
            'id' => 'required|string|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The ID parameter is required.',
            'id.exists' => 'The specified user does not exist.',
        ];
    }
}
