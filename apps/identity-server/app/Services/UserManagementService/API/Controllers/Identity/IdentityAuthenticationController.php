<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\Application\Contracts\IdentityAccessServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class IdentityAuthenticationController extends Controller
{
    public function __construct(private readonly IdentityAccessServiceInterface $identity) {}

    public function login(Request $request): JsonResponse
    {
        $input = $request->validate([
            'project' => ['nullable', 'string', 'max:255'],
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        return response()->json(['data' => $this->identity->login($input, $request->ip(), $request->userAgent())]);
    }

    public function context(Request $request): JsonResponse
    {
        $input = $request->validate([
            'project' => ['nullable', 'string', 'max:255'],
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
        ]);

        return response()->json(['data' => $this->identity->authenticationContext(
            $input['client_id'],
            $input['client_secret'],
            $input['project'] ?? null,
        )]);
    }

    public function register(Request $request): JsonResponse
    {
        $input = $request->validate([
            'project' => ['nullable', 'string', 'max:255'],
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
            'username' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        return response()->json([
            'data' => $this->identity->register($input, $request->ip(), $request->userAgent()),
        ], 201);
    }

    public function sandboxSession(Request $request): JsonResponse
    {
        $input = $request->validate([
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
        ]);

        return response()->json([
            'data' => $this->identity->createSandboxSession(
                $input['client_id'],
                $input['client_secret'],
                $request->ip(),
                $request->userAgent(),
            ),
        ], 201);
    }

    public function refresh(Request $request): JsonResponse
    {
        $input = $request->validate([
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
            'refresh_token' => ['required', 'string', 'min:64'],
        ]);

        return response()->json(['data' => $this->identity->refresh($input, $request->ip(), $request->userAgent())]);
    }

    public function resendEmailVerification(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->identity->resendEmailVerification(
            (string) $request->user()->getAuthIdentifier(),
        )]);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $input = $request->validate(['code' => ['required', 'digits:6']]);
        $this->identity->verifyEmail((string) $request->user()->getAuthIdentifier(), $input['code']);

        return response()->json(['data' => ['message' => 'Email address verified.']]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $input = $request->validate([
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
            'email' => ['required', 'email'],
        ]);

        return response()->json(['data' => $this->identity->requestPasswordReset(
            $input['client_id'], $input['client_secret'], $input['email'],
        )]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $input = $request->validate([
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'min:64'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);
        $this->identity->resetPassword(
            $input['client_id'], $input['client_secret'], $input['email'], $input['token'], $input['password'],
        );

        return response()->json(['data' => ['message' => 'Password reset completed.']]);
    }

    public function introspect(Request $request): JsonResponse
    {
        $input = $request->validate([
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
            'token' => ['required', 'string'],
        ]);

        return response()->json($this->identity->introspect($input['client_id'], $input['client_secret'], $input['token']));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        if ($token !== null) {
            $this->identity->logout($token);
        }

        return response()->json(['data' => ['message' => 'Session revoked.']]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->identity->currentIdentity((string) $request->user()->getAuthIdentifier(), (string) $request->bearerToken())]);
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->identity->listSessions((string) $request->user()->getAuthIdentifier(), (string) $request->bearerToken())]);
    }

    public function revokeSession(Request $request, string $session): JsonResponse
    {
        $this->identity->revokeSession((string) $request->user()->getAuthIdentifier(), $session);

        return response()->json(['data' => ['message' => 'Session revoked.']]);
    }

    public function acceptInvitation(Request $request): JsonResponse
    {
        $input = $request->validate([
            'invitation_token' => ['required', 'string', 'min:64'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        return response()->json(['data' => $this->identity->acceptInvitation($input)], 201);
    }

    public function syncPermissionManifest(Request $request): JsonResponse
    {
        $input = $request->validate([
            'client_id' => ['required', 'uuid'],
            'client_secret' => ['required', 'string', 'min:32'],
            'permissions' => ['present', 'array', 'max:500'],
            'permissions.*.key' => ['required', 'string', 'regex:/^[a-z0-9]+(?:[._:-][a-z0-9]+)*$/', 'max:160', 'distinct'],
            'permissions.*.name' => ['nullable', 'string', 'max:255'],
            'permissions.*.description' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(['data' => $this->identity->syncOwnPermissionManifest($input['client_id'], $input['client_secret'], $input['permissions'])]);
    }
}
