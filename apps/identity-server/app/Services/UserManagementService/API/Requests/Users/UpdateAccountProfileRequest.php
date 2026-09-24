<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use App\Services\UserManagementService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use Zolta\Http\Request\BaseRequest;

final class UpdateAccountProfileRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity() && $this->authenticatedProjectId() !== null;
    }

    public function trustedData(): array
    {
        return [
            'user_id' => $this->authenticatedUserId(),
            'project_id' => $this->authenticatedProjectId(),
        ];
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:100',
            'email' => 'required|email',
            'avatar_url' => 'nullable|url',
        ];
    }
}
