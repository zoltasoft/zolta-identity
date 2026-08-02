# zolta/identity-laravel

Remote-only token introspection and signed-webhook verification for Laravel applications consuming Zolta Identity.

Install the package and configure one API client per live or sandbox project:

```dotenv
IDENTITY_API_URL=https://identity.example.com
IDENTITY_PROJECT=my-application
IDENTITY_CLIENT_ID=<live-api-client-id>
IDENTITY_CLIENT_SECRET=<live-api-client-secret>

IDENTITY_SANDBOX_API_URL=https://identity.example.com
IDENTITY_SANDBOX_PROJECT=my-application-sandbox
IDENTITY_SANDBOX_CLIENT_ID=<sandbox-api-client-id>
IDENTITY_SANDBOX_CLIENT_SECRET=<sandbox-api-client-secret>

IDENTITY_WEBHOOK_SECRETS=<live-secret>,<sandbox-secret>
```

Protect a route with `identity.introspect` or `identity.introspect:permission.key`. The middleware tries configured connections, accepts only a token belonging to that connection's project, exposes `Zolta\Identity\Laravel\IntrospectedIdentity` on the request attribute `identity`, and sets an `IdentityPrincipal` as the request user. It caches active results only and fails closed with 503 when all configured services are unavailable.

Use `WebhookSignatureVerifier` with the raw request body and the `X-Identity-Timestamp` and `X-Identity-Signature` headers. Reject timestamps outside the configured tolerance and deduplicate `X-Identity-Event-Id` before processing cleanup.
