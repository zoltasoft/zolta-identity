<?php

namespace App\Services\UserManagementService\API\Requests\Authentication;

use App\Services\UserManagementService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use Zolta\Http\Request\BaseRequest;

final class ExportAccountDataRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity();
    }

    public function trustedData(): array
    {
        return ['user_id' => $this->authenticatedUserId()];
    }

    public function rules(): array
    {
        return [];
    }
}
