<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Identity;

final class IdentityInstallationOperationRequest extends IdentityOperationRequest
{
    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity();
    }

    /** @return array<string, mixed> */
    public function routeParams(): array
    {
        return $this->route('user') !== null
            ? ['user' => ['type' => 'string', 'required' => true]]
            : [];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->operation() === 'installation.users.update'
            ? [
                'user' => ['required', 'uuid'],
                'is_system_admin' => ['required', 'boolean'],
                'locked' => ['required', 'boolean'],
            ]
            : [];
    }
}
