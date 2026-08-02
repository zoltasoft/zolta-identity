<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\Application\Contracts\IdentityAccessServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

final class IdentityProjectController extends Controller
{
    public function __construct(private readonly IdentityAccessServiceInterface $identity) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->identity->listProjects($this->userId($request))]);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('identity_projects', 'slug')],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->identity->createProject($this->userId($request), $input)], 201);
    }

    public function updateRegistration(Request $request, string $project): JsonResponse
    {
        $input = $request->validate([
            'registration_mode' => ['required', Rule::in(['invite_only', 'public'])],
            'registration_role_id' => ['nullable', 'uuid'],
        ]);
        $this->identity->updateProjectRegistration(
            $this->userId($request),
            $project,
            $input['registration_mode'],
            $input['registration_role_id'] ?? null,
        );

        return response()->json(['data' => ['message' => 'Registration policy updated.']]);
    }

    public function show(Request $request, string $project): JsonResponse
    {
        return response()->json(['data' => $this->identity->projectDetails($this->userId($request), $project)]);
    }

    public function storeClient(Request $request, string $project): JsonResponse
    {
        $input = $request->validate(['name' => ['required', 'string', 'max:255']]);

        return response()->json(['data' => $this->identity->createClient($this->userId($request), $project, $input['name'])], 201);
    }

    public function rotateClient(Request $request, string $project, string $client): JsonResponse
    {
        return response()->json(['data' => $this->identity->rotateClientSecret($this->userId($request), $project, $client)]);
    }

    public function setClientStatus(Request $request, string $project, string $client): JsonResponse
    {
        $input = $request->validate(['status' => ['required', Rule::in(['active', 'disabled'])]]);
        $this->identity->setClientStatus($this->userId($request), $project, $client, $input['status']);

        return response()->json(['data' => ['message' => 'Client status updated.']]);
    }

    public function syncManifest(Request $request, string $project, string $client): JsonResponse
    {
        $input = $request->validate([
            'permissions' => ['present', 'array', 'max:500'],
            'permissions.*.key' => ['required', 'string', 'regex:/^[a-z0-9]+(?:[._:-][a-z0-9]+)*$/', 'max:160', 'distinct'],
            'permissions.*.name' => ['nullable', 'string', 'max:255'],
            'permissions.*.description' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->identity->syncPermissionManifest($this->userId($request), $project, $client, $input['permissions'])]);
    }

    public function storeRole(Request $request, string $project): JsonResponse
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('identity_project_roles', 'slug')->where('project_id', $project)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->identity->createRole($this->userId($request), $project, $input)], 201);
    }

    public function storePermission(Request $request, string $project): JsonResponse
    {
        $input = $request->validate([
            'key' => ['required', 'string', 'regex:/^[a-z0-9]+(?:[._:-][a-z0-9]+)*$/', 'max:160', Rule::unique('identity_project_permissions', 'key')->where('project_id', $project)],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->identity->createPermission($this->userId($request), $project, $input)], 201);
    }

    public function setRolePermissions(Request $request, string $project, string $role): JsonResponse
    {
        $input = $request->validate(['permission_ids' => ['present', 'array'], 'permission_ids.*' => ['uuid', 'distinct']]);
        $this->identity->setRolePermissions($this->userId($request), $project, $role, $input['permission_ids']);

        return response()->json(['data' => ['message' => 'Role permissions updated.']]);
    }

    public function invite(Request $request, string $project): JsonResponse
    {
        $input = $request->validate(['email' => ['required', 'email'], 'is_admin' => ['sometimes', 'boolean']]);

        return response()->json(['data' => $this->identity->invite($this->userId($request), $project, $input['email'], (bool) ($input['is_admin'] ?? false))], 201);
    }

    public function setMembershipAccess(Request $request, string $project, string $membership): JsonResponse
    {
        $input = $request->validate([
            'role_ids' => ['present', 'array'],
            'role_ids.*' => ['uuid', 'distinct'],
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['uuid', 'distinct'],
            'is_admin' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);
        $this->identity->setMembershipAccess(
            $this->userId($request), $project, $membership,
            $input['role_ids'], $input['permission_ids'], $input['is_admin'], $input['status'],
        );

        return response()->json(['data' => ['message' => 'Membership access updated.']]);
    }

    public function destroyMembership(Request $request, string $project, string $membership): JsonResponse
    {
        $this->identity->removeMembership($this->userId($request), $project, $membership);

        return response()->json(['data' => ['message' => 'Membership removed.']]);
    }

    public function audit(Request $request, string $project): JsonResponse
    {
        $input = $request->validate(['limit' => ['sometimes', 'integer', 'min:1', 'max:250']]);

        return response()->json(['data' => $this->identity->listAuditEvents($this->userId($request), $project, (int) ($input['limit'] ?? 100))]);
    }

    private function userId(Request $request): string
    {
        return (string) $request->user()->getAuthIdentifier();
    }
}
