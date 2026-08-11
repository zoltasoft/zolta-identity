<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Identity;

use Zolta\Http\Request\BaseRequest;

final class IdentityHostedApplicationConfigurationRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function routeParams(): array
    {
        return ['application' => ['type' => 'string', 'required' => true]];
    }

    /** @return array<string, mixed> */
    public function trustedData(): array
    {
        return [
            'application' => (string) $this->route('application'),
            'by_client' => str_ends_with((string) $this->route()?->getName(), 'client.configuration'),
        ];
    }
}
