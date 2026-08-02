<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\RateLimitingServiceInterface;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Laravel-based rate limiting service implementation.
 * Uses Laravel's cache repository (Redis, Memcached, file, etc.)
 * and stores attempts + expiration timestamps.
 */
class LaravelRateLimitingService implements RateLimitingServiceInterface
{
    public function __construct(
        private Cache $cache
    ) {}

    /**
     * {@inheritdoc}
     */
    public function tooManyAttempts(string $key, int $maxAttempts = 5, int $decayMinutes = 1): bool
    {
        return $this->getAttempts($key) >= $maxAttempts;
    }

    /**
     * {@inheritdoc}
     */
    public function hit(string $key, int $decayMinutes = 1): void
    {
        $ttlSeconds = $decayMinutes * 60;

        $data = $this->getRateLimitData($key);
        $attempts = $data['attempts'] + 1;

        $this->store($key, $attempts, $ttlSeconds);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(string $key): void
    {
        $this->cache->forget($this->cacheKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function availableIn(string $key, int $maxAttempts = 5, int $decayMinutes = 1): ?int
    {
        $data = $this->getRateLimitData($key);

        if ($data['attempts'] < $maxAttempts) {
            return null; // Not rate limited
        }

        return max(0, $data['expires_at'] - time());
    }

    /**
     * Get the number of attempts for the given key.
     */
    private function getAttempts(string $key): int
    {
        return $this->getRateLimitData($key)['attempts'];
    }

    /**
     * Persist updated attempts + expiration timestamp.
     */
    private function store(string $key, int $attempts, int $ttlSeconds): void
    {
        $this->cache->put(
            $this->cacheKey($key),
            [
                'attempts' => $attempts,
                'expires_at' => time() + $ttlSeconds,
            ],
            $ttlSeconds
        );
    }

    /**
     * Get rate limit data, fallback if absent.
     *
     * @return array{attempts: int, expires_at: int}
     */
    private function getRateLimitData(string $key): array
    {
        $data = $this->cache->get($this->cacheKey($key));

        if (
            ! is_array($data) ||
            ! isset($data['attempts'], $data['expires_at'])
        ) {
            return [
                'attempts' => 0,
                'expires_at' => time(), // Expired immediately
            ];
        }

        return $data;
    }

    /**
     * Build the rate limit cache key.
     */
    private function cacheKey(string $key): string
    {
        return 'rate_limit_'.md5($key);
    }
}
