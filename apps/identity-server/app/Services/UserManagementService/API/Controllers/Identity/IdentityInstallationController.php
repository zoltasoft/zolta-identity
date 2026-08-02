<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\Application\Contracts\IdentityAccessServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class IdentityInstallationController extends Controller
{
    public function __construct(private readonly IdentityAccessServiceInterface $identity) {}

    public function users(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->identity->listInstallationUsers((string) $request->user()->getAuthIdentifier())]);
    }

    public function updateUser(Request $request, string $user): JsonResponse
    {
        $input = $request->validate([
            'is_system_admin' => ['required', 'boolean'],
            'locked' => ['required', 'boolean'],
        ]);
        $this->identity->updateInstallationUser(
            (string) $request->user()->getAuthIdentifier(),
            $user,
            $input['is_system_admin'],
            $input['locked'],
        );

        return response()->json(['data' => ['message' => 'Installation user updated.']]);
    }
}
