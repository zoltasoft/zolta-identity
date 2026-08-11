<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Application\Exceptions\IdentityAuthenticationException;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use Illuminate\Http\Request;

final class IdentityClientAuthenticator
{
    public function authenticate(string $clientId, string $clientSecret): IdentityProjectClient
    {
        $client = IdentityProjectClient::query()
            ->with('project')
            ->where('status', 'active')
            ->find($clientId);

        if (! $client
            || (! $this->isHostedInternalRequest() && ! hash_equals($client->secret_hash, hash('sha256', $clientSecret)))
            || $client->project?->status !== 'active') {
            throw new IdentityAuthenticationException('Invalid client credentials.');
        }

        return $client;
    }

    private function isHostedInternalRequest(): bool
    {
        return app(Request::class)->attributes->get('identity_hosted_internal') === true;
    }
}
