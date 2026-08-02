<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Transformers;

use Zolta\Domain\Contracts\Transformer;

final class IdentifierNormalizer extends Transformer
{
    /**
     * Transform input identifier (username or email) for VO hydration.
     */
    public function transform(mixed $value, array $options = []): string
    {
        if (! is_string($value)) {
            throw new \InvalidArgumentException('IdentifierNormalizer expects a string.');
        }

        $normalized = trim($value);

        // Optional: if it's an email, lowercase fully
        if (str_contains($normalized, '@')) {
            $normalized = mb_strtolower($normalized);
        }

        // Optional: further rules like removing spaces for usernames
        $normalized = preg_replace('/\s+/', '', $normalized);

        return $normalized;
    }
}
