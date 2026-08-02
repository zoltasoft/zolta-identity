<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

/**
 * Application layer interface for rate limiting functionality.
 * Implementation should be provided by infrastructure layer.
 */
interface RateLimitingServiceInterface
{
    /**
     * Check if the given key has exceeded the rate limit.
     *
     * @param  string  $key  Unique identifier for rate limiting (e.g., email + IP)
     * @param  int  $maxAttempts  Maximum number of attempts allowed
     * @param  int  $decayMinutes  Time window in minutes
     * @return bool True if rate limit exceeded, false otherwise
     */
    public function tooManyAttempts(string $key, int $maxAttempts = 5, int $decayMinutes = 1): bool;

    /**
     * Record a rate limiting attempt for the given key.
     *
     * @param  string  $key  Unique identifier for rate limiting
     * @param  int  $decayMinutes  Time window in minutes
     */
    public function hit(string $key, int $decayMinutes = 1): void;

    /**
     * Clear rate limiting attempts for the given key.
     *
     * @param  string  $key  Unique identifier for rate limiting
     */
    public function clear(string $key): void;

    /**
     * Get the number of seconds remaining until rate limit resets.
     *
     * @param  string  $key  Unique identifier for rate limiting
     * @param  int  $maxAttempts  Maximum number of attempts allowed
     * @param  int  $decayMinutes  Time window in minutes
     * @return int|null Seconds remaining, or null if not rate limited
     */
    public function availableIn(string $key, int $maxAttempts = 5, int $decayMinutes = 1): ?int;
}
