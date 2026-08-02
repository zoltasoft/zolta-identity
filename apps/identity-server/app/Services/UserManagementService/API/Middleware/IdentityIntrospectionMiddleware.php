<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Middleware;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ReadIdentitySessions;
use App\Services\UserManagementService\Application\DTOs\External\IntrospectedIdentity;
use App\Services\UserManagementService\Application\Exceptions\IdentityAuthenticationException;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class IdentityIntrospectionMiddleware
{
    public function __construct(
        private readonly ReadIdentitySessions $identity,
    ) {}

    public function handle(Request $request, Closure $next, ?string $requiredPermission = null): Response
    {
        $token = $request->bearerToken();
        $baseUrl = (string) config('identity.consumer.base_url');
        $clientId = (string) config('identity.consumer.client_id');
        $clientSecret = (string) config('identity.consumer.client_secret');
        $local = (bool) config('identity.consumer.local', false);
        if (! $token || (! $local && ! $baseUrl) || ! $clientId || ! $clientSecret) {
            return response()->json(['message' => 'Identity authentication is not configured or missing.'], 401);
        }

        $cacheKey = 'identity:introspection:'.hash('sha256', $token);
        $payload = Cache::get($cacheKey);
        if (! is_array($payload)) {
            if ($local) {
                try {
                    $payload = $this->identity->introspect($clientId, $clientSecret, $token);
                } catch (IdentityAuthenticationException) {
                    return response()->json(['message' => 'Identity API client authentication failed.'], 503);
                } catch (Throwable) {
                    return response()->json(['message' => 'The identity service is unavailable.'], 503);
                }
            } else {
                try {
                    $response = Http::acceptJson()->timeout(5)->post(rtrim($baseUrl, '/').'/api/v1/identity/auth/introspect', [
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'token' => $token,
                    ]);
                } catch (ConnectionException) {
                    return response()->json(['message' => 'The identity service is unavailable.'], 503);
                }

                if (! $response->successful()) {
                    return response()->json(['message' => 'Identity token validation failed.'], 503);
                }
                $payload = $response->json();
            }

            if (($payload['active'] ?? false) === true) {
                $ttl = min(
                    (int) config('identity.consumer.introspection_cache_seconds', 30),
                    max(1, (int) $payload['exp'] - now()->getTimestamp()),
                );
                Cache::put($cacheKey, $payload, $ttl);
            }
        }

        if (($payload['active'] ?? false) !== true) {
            return response()->json(['message' => 'The access token is inactive.'], 401);
        }

        $identity = IntrospectedIdentity::fromIntrospection($payload);
        if ($requiredPermission !== null && ! $identity->can($requiredPermission)) {
            return response()->json(['message' => 'The required permission is missing.'], 403);
        }

        $principal = new GenericUser([
            'id' => $identity->userId,
            'email' => $identity->email,
            'name' => $identity->username,
            'username' => $identity->username,
        ]);
        $request->setUserResolver(static fn (): GenericUser => $principal);
        Auth::setUser($principal);
        $request->attributes->set('identity', $identity);

        return $next($request);
    }
}
