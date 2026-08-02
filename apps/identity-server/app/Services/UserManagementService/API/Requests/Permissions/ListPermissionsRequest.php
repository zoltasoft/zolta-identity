<?php

namespace App\Services\UserManagementService\API\Requests\Permissions;

use Zolta\Http\Request\BaseRequest;

final class ListPermissionsRequest extends BaseRequest
{
    public function authorize(): bool
    {

        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function queryOptions(): array
    {
        return [
            'default_include' => ['roles'],
            'strict' => true,
            'allowed_filters' => ['name'],
            'allowed_sorts' => ['name'],
        ];
    }

    public function queryParams(): array
    {
        return [
            'filter' => ['type' => 'array', 'required' => false],
            'include' => ['type' => 'array', 'required' => false],
            'sort' => ['type' => 'array', 'required' => false],
            'page' => ['type' => 'integer', 'required' => false],
            'per_page' => ['type' => 'integer', 'required' => false],
        ];
    }

    public function rules(): array
    {
        return [
            'filter' => 'sometimes|array',
            'include' => 'sometimes|array',
            'include.*' => 'string',
            'sort' => 'sometimes|array',
            'sort.*' => 'string',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
