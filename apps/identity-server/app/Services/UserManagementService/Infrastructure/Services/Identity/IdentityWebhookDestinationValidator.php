<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Application\Exceptions\IdentityAuthorizationException;

final class IdentityWebhookDestinationValidator
{
    public function assertValid(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new IdentityAuthorizationException('The webhook URL is invalid.');
        }

        if (app()->environment('production') && $scheme !== 'https') {
            throw new IdentityAuthorizationException('Production webhook URLs must use HTTPS.');
        }

        if (! app()->environment('production')) {
            return;
        }

        $resolved = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if ($host === 'localhost'
            || str_ends_with($host, '.localhost')
            || filter_var(
                $resolved,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
            ) === false) {
            throw new IdentityAuthorizationException(
                'Private webhook destinations are not allowed in production.',
            );
        }
    }
}
