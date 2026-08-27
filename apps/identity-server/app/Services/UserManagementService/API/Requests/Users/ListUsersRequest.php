<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

final class ListUsersRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function queryOptions(): array
    {
        return [
            'include' => ['socialAccounts'],
            'filters' => ['email', 'username'],
            'sorts' => ['email', 'username', 'created_at'],
            'strict' => true,
        ];
    }

    public function routeParams(): array
    {
        return [];
    }

    public function queryParams(): array
    {
        return [
            'filter' => ['type' => 'array', 'required' => false, 'delimiter' => ','],
            'include' => ['type' => 'array', 'required' => false, 'delimiter' => ','],
            'sort' => ['type' => 'array', 'required' => false, 'delimiter' => ','],
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

    public function messages(): array
    {
        return [
            'page.integer' => 'The page parameter must be an integer.',
            'page.min' => 'The page parameter must be at least 1.',
            'per_page.integer' => 'The per_page parameter must be an integer.',
            'per_page.min' => 'The per_page parameter must be at least 1.',
            'per_page.max' => 'The per_page parameter may not be greater than 100.',
        ];
    }
}
