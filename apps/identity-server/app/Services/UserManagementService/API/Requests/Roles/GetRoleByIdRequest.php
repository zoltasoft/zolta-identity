<?php

namespace App\Services\UserManagementService\API\Requests\Roles;

use Zolta\Http\Request\BaseRequest;

class GetRoleByIdRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function queryOptions(): array
    {
        return [
            'default_include' => ['permissions'],
            'strict' => true,
            'allowed_filters' => ['role'],
            'allowed_sorts' => ['role'],
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

    /**
     * Define route parameters to merge.
     */
    public function routeParams(): array
    {
        return [
            'id' => [
                'type' => 'string',
                'required' => true,
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string|exists:roles,id',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'id.required' => 'The ID parameter is required.',
            'id.exists' => 'The specified role does not exist.',
        ];
    }
}
