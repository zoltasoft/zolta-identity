<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

final class ProvisionUserAccessRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $this->authorizeAction(['admin.access']);

        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|uuid',
            'role_id' => 'required|uuid',
            'permission_ids' => 'sometimes|array',
            'permission_ids.*' => 'uuid',
            'attach_permissions_to_role' => 'sometimes|boolean',
        ];
    }
}
