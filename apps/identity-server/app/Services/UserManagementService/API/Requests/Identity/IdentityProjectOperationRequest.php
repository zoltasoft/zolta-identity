<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Identity;

use Illuminate\Validation\Rule;

final class IdentityProjectOperationRequest extends IdentityOperationRequest
{
    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity();
    }

    /** @return array<string, mixed> */
    public function routeParams(): array
    {
        $params = [];
        foreach (['project', 'client', 'webhook', 'role', 'membership'] as $parameter) {
            if ($this->route($parameter) !== null) {
                $params[$parameter] = ['type' => 'string', 'required' => true];
            }
        }

        return $params;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $routeRules = array_fill_keys(array_keys($this->routeParams()), ['required', 'uuid']);
        $bodyRules = match ($this->operation()) {
            'projects.store' => [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('identity_projects', 'slug')],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            'projects.registration.update' => [
                'registration_mode' => ['required', Rule::in(['invite_only', 'public'])],
                'registration_role_id' => ['nullable', 'uuid'],
            ],
            'projects.environment.update' => [
                'mode' => ['required', Rule::in(['live', 'sandbox'])],
                'sandbox_ttl_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            ],
            'projects.webhooks.store' => $this->webhookRules(),
            'projects.webhooks.update' => [
                ...$this->webhookRules(),
                'status' => ['required', Rule::in(['active', 'disabled'])],
            ],
            'projects.clients.store' => ['name' => ['required', 'string', 'max:255']],
            'projects.clients.status' => ['status' => ['required', Rule::in(['active', 'disabled'])]],
            'projects.clients.manifest' => $this->manifestRules(),
            'projects.roles.store' => [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('identity_project_roles', 'slug')->where('project_id', (string) $this->route('project'))],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            'projects.permissions.store' => [
                'key' => ['required', 'string', 'regex:/^[a-z0-9]+(?:[._:-][a-z0-9]+)*$/', 'max:160', Rule::unique('identity_project_permissions', 'key')->where('project_id', (string) $this->route('project'))],
                'name' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            'projects.roles.permissions' => [
                'permission_ids' => ['present', 'array'],
                'permission_ids.*' => ['uuid', 'distinct'],
            ],
            'projects.invitations.store' => [
                'email' => ['required', 'email'],
                'is_admin' => ['sometimes', 'boolean'],
            ],
            'projects.memberships.access' => [
                'role_ids' => ['present', 'array'],
                'role_ids.*' => ['uuid', 'distinct'],
                'permission_ids' => ['present', 'array'],
                'permission_ids.*' => ['uuid', 'distinct'],
                'is_admin' => ['required', 'boolean'],
                'status' => ['required', Rule::in(['active', 'suspended'])],
            ],
            'projects.audit' => ['limit' => ['sometimes', 'integer', 'min:1', 'max:250']],
            default => [],
        };

        return [...$routeRules, ...$bodyRules];
    }

    /** @return array<string, mixed> */
    private function webhookRules(): array
    {
        return [
            'url' => ['required', 'url:http,https', 'max:2048'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', Rule::in(['identity.user.expired', 'identity.user.deletion_requested'])],
        ];
    }

    /** @return array<string, mixed> */
    private function manifestRules(): array
    {
        return [
            'permissions' => ['present', 'array', 'max:500'],
            'permissions.*.key' => ['required', 'string', 'regex:/^[a-z0-9]+(?:[._:-][a-z0-9]+)*$/', 'max:160', 'distinct'],
            'permissions.*.name' => ['nullable', 'string', 'max:255'],
            'permissions.*.description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
