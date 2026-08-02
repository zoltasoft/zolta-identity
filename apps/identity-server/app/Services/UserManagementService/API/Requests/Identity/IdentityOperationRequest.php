<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Identity;

use App\Services\UserManagementService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use Zolta\Http\Request\BaseRequest;

abstract class IdentityOperationRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    /** @return array<string, mixed> */
    public function trustedData(): array
    {
        return [
            'operation' => $this->operation(),
            'input' => array_merge($this->validated(), $this->getRouteParameters()),
            'actor_user_id' => $this->hasAuthenticatedIdentity() ? $this->authenticatedUserId() : null,
            'access_token' => $this->bearerToken(),
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ];
    }

    protected function operation(): string
    {
        $name = (string) $this->route()?->getName();

        return str_starts_with($name, 'identity.')
            ? substr($name, strlen('identity.'))
            : $name;
    }
}
