<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use Zolta\Http\Request\BaseRequest;

final class UpdateSecurityPreferencesRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function trustedData(): array
    {
        return [
            'user_id' => (string) $this->user()?->getAuthIdentifier(),
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
