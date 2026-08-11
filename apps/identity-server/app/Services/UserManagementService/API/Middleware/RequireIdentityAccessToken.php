<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

final class RequireIdentityAccessToken
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var PersonalAccessToken|null $token */
        $token = $request->user()?->currentAccessToken();
        if (! $token || ! $token->identity_project_id || ! $token->identity_client_id) {
            return response()->json(['message' => 'A project-scoped identity token is required.'], 401);
        }

        $routeProjectId = $request->route('project');
        if ($routeProjectId !== null
            && (string) $routeProjectId !== (string) $token->identity_project_id
            && ! (bool) $request->user()?->is_system_admin) {
            return response()->json([
                'message' => 'The identity token is not authorized for this project.',
            ], 403);
        }

        return $next($request);
    }
}
