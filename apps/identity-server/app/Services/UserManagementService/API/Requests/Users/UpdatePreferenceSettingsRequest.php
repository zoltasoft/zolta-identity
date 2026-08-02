<?php

namespace App\Services\UserManagementService\API\Requests\Users;

use App\Services\UserManagementService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use Zolta\Http\Request\BaseRequest;

final class UpdatePreferenceSettingsRequest extends BaseRequest
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
            'theme' => 'required|string|in:light,dark,system',
            'language' => 'required|string|max:10',
        ];
    }
}
