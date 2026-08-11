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

        if ($host === 'localhost'
            || str_ends_with($host, '.localhost')) {
            throw new IdentityAuthorizationException(
                'Private webhook destinations are not allowed in production.',
            );
        }

        $this->resolvePublicAddresses($host);
    }

    /** @return list<string> */
    public function resolvePublicAddresses(string $host): array
    {
        $host = trim($host, '[]');
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $addresses = [$host];
        } else {
            $records = dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
            $addresses = collect($records)
                ->map(static fn (array $record): ?string => $record['ip'] ?? $record['ipv6'] ?? null)
                ->filter(static fn (?string $address): bool => $address !== null)
                ->unique()
                ->values()
                ->all();
        }

        if ($addresses === []) {
            throw new IdentityAuthorizationException('The webhook host could not be resolved.');
        }

        if (app()->environment('production')) {
            foreach ($addresses as $address) {
                if (filter_var(
                    $address,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
                ) === false) {
                    throw new IdentityAuthorizationException(
                        'Private webhook destinations are not allowed in production.',
                    );
                }
            }
        }

        return $addresses;
    }
}
