<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Identity;

use App\Services\UserManagementService\Application\Exceptions\IdentityResourceNotFoundException;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityHostedApplication;

final class IdentityHostedAuthenticationOperationRequest extends IdentityOperationRequest
{
    public function authorize(): bool
    {
        return ! in_array($this->operation(), ['auth.handoff.create', 'auth.account.intent.create'], true) || $this->hasAuthenticatedIdentity();
    }

    /** @return array<string, mixed> */
    public function routeParams(): array
    {
        return ['application' => ['type' => 'string', 'required' => true]];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return match ($this->operation()) {
            'auth.context' => ['connection' => ['sometimes', 'in:primary,sandbox']],
            'auth.login' => ['email' => ['required', 'email'], 'password' => ['required', 'string']],
            'auth.register' => [
                'username' => ['required', 'string', 'min:2', 'max:100'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:12', 'confirmed'],
                'terms_accepted' => ['sometimes', 'boolean'],
            ],
            'auth.social' => [
                'provider' => ['required', 'in:google'],
                'access_token' => ['required', 'string', 'max:4096'],
                'terms_accepted' => ['sometimes', 'boolean'],
            ],
            'auth.sandbox-session' => [],
            'auth.refresh' => ['refresh_token' => ['required', 'string', 'min:64']],
            'auth.password.forgot' => ['email' => ['required', 'email']],
            'auth.password.reset' => [
                'email' => ['required', 'email'],
                'token' => ['required', 'string', 'min:64'],
                'password' => ['required', 'string', 'min:12', 'confirmed'],
            ],
            'auth.handoff.create' => ['connection' => ['required', 'in:primary,sandbox']],
            'auth.authorization.intent.consume' => ['intent' => ['required', 'string', 'min:64']],
            'auth.account.intent.create' => [],
            'auth.account.intent.consume' => ['intent' => ['required', 'string', 'min:64']],
            'auth.logout.intent.consume' => ['intent' => ['required', 'string', 'min:64']],
            default => [],
        };
    }

    /** @return array<string, mixed> */
    public function trustedData(): array
    {
        $application = IdentityHostedApplication::query()
            ->with(['primaryClient.project', 'sandboxClient.project'])
            ->where('key', (string) $this->route('application'))
            ->where('status', 'active')
            ->first();
        if ($application === null) {
            throw new IdentityResourceNotFoundException('Identity hosted application');
        }

        $sandbox = $this->operation() === 'auth.sandbox-session'
            || in_array($this->operation(), ['auth.context', 'auth.handoff.create'], true)
                && $this->input('connection') === 'sandbox';
        $client = $sandbox ? $application->sandboxClient : $application->primaryClient;
        if ($client === null || $client->status !== 'active' || $client->project?->status !== 'active') {
            throw new IdentityResourceNotFoundException('Identity hosted application');
        }

        $input = $this->validated() + [
            'client_id' => $client->id,
            'client_secret' => '',
        ];
        if (in_array($this->operation(), ['auth.authorization.intent.consume', 'auth.account.intent.create', 'auth.account.intent.consume', 'auth.logout.intent.consume'], true)) {
            $input['hosted_application_id'] = $application->id;
        }
        if (in_array($this->operation(), ['auth.register', 'auth.social'], true)) {
            $authentication = $application->authentication ?? [];
            if ($this->operation() === 'auth.register'
                && ($authentication['terms_required'] ?? false)
                && ! ($input['terms_accepted'] ?? false)) {
                abort(422, 'You must accept the terms of service to create an account.');
            }
            $input['hosted_application_id'] = $application->id;
            $input['terms_required'] = (bool) ($authentication['terms_required'] ?? false);
            $input['terms_url'] = $authentication['terms_url'] ?? null;
        }
        if ($this->operation() === 'auth.handoff.create') {
            $input['redirect_uri'] = $application->callback_url;
        }

        return [
            'operation' => $this->operation(),
            'input' => $input,
            'actor_user_id' => $this->hasAuthenticatedIdentity() ? $this->authenticatedUserId() : null,
            'access_token' => $this->bearerToken(),
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ];
    }

    protected function operation(): string
    {
        $name = (string) $this->route()?->getName();

        return str_replace('identity.hosted_applications.', 'auth.', $name);
    }
}
