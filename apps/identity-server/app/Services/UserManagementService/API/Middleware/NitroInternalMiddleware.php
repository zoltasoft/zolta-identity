<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class NitroInternalMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('identity.hosted_applications.internal_token', '');

        if ($expectedToken === '') {
            return response()->json([
                'message' => 'Hosted application resolution is not configured.',
            ], 503);
        }

        $providedToken = (string) $request->header('X-Internal-Token', '');

        if (! hash_equals($expectedToken, $providedToken)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $request->attributes->set('identity_hosted_internal', true);

        return $next($request);
    }
}
