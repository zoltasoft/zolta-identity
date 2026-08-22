<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Identity;

use App\Services\UserManagementService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use Zolta\Http\Request\BaseRequest;

final class RemoveIdentityHostedApplicationLogoRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity();
    }

    /** @return array<string, array<string, bool|string>> */
    public function routeParams(): array
    {
        return [
            'project' => ['type' => 'string', 'required' => true],
            'hosted_application' => ['type' => 'string', 'required' => true],
        ];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'project' => ['required', 'uuid'],
            'hosted_application' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function trustedData(): array
    {
        $request = request();

        return [
            'actor_user_id' => $this->authenticatedUserId(),
            'project_id' => (string) $request->route('project'),
            'application_id' => (string) $request->route('hosted_application'),
        ];
    }
}
