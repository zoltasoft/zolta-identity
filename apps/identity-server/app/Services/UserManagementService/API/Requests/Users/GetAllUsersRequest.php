<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

class GetAllUsersRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'filters' => ['array'],
            'filters.limit' => ['integer', 'min:1', 'max:100'],
            'filters.page' => ['integer', 'min:1'],
            'filters.status' => ['nullable', 'string'],
            'filters.*' => ['sometimes'],
            'include' => ['array'],
        ];
    }
}
