<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use App\Services\UserManagementService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use Zolta\Http\Request\BaseRequest;

final class UpdateSecurityPreferencesRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity();
    }

    public function trustedData(): array
    {
        return [
            'user_id' => $this->authenticatedUserId(),
        ];
    }

    public function rules(): array
    {
        return [
            'two_factor_enabled' => 'required|boolean',
            'login_alerts_enabled' => 'required|boolean',
            'backup_email' => 'nullable|email',
        ];
    }
}
