<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProtectIdentityAdministration
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isAdministrativeRoute($request)) {
            return $next($request);
        }

        $user = $request->user('sanctum');
        if ($user !== null && ! (bool) $user->is_system_admin) {
            abort(403, 'System administrator access is required.');
        }

        return $next($request);
    }

    private function isAdministrativeRoute(Request $request): bool
    {
        $path = $request->path();

        if ($path === 'api/users') {
            return true;
        }

        if (str_starts_with($path, 'api/users/profile')) {
            return false;
        }

        return preg_match('#^api/users/(by-email/|[^/]+(?:/email)?$)#', $path) === 1;
    }
}
