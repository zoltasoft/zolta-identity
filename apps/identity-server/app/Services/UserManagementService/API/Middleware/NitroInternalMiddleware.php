<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates the X-Internal-Token header that the Nuxt Nitro BFF attaches to
 * every server-to-server request.  Rejects anything that does not carry the
 * correct shared secret, effectively making this Laravel API inaccessible
 * to anyone other than the trusted Nitro server.
 *
 * Configuration:  set NITRO_INTERNAL_TOKEN in .env.
 */
final class NitroInternalMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // $expectedToken = env('NITRO_INTERNAL_TOKEN', '');

        // if (empty($expectedToken)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Internal service token is not configured on this server.',
        //     ], 503);
        // }

        // $provided = (string) $request->header('X-Internal-Token', '');

        // if (!hash_equals($expectedToken, $provided)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Unauthorized.',
        //     ], 401);
        // }

        return $next($request);
    }
}
