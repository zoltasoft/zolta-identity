<?php

namespace App\Services\UserManagementService\Infrastructure\DTOs;

/**
 * Data Transfer Object representing an social user.
 */
final class OAuthUser
{
    /**
     * @param  string  $providerUserId  The social user's id.
     * @param  string  $name  The social user's name.
     * @param  string  $email  The social user's email address.
     * @param  string  $accessToken  The social user's access_token.
     * @param  string|null  $refreshToken  The social user's refresh_token (may be null for some providers).
     * @param  string|null  $avatarUrl  The social user's avatar_url (may be null when provider omits it).
     */
    public function __construct(
        public readonly string $providerUserId,
        public readonly string $name,
        public readonly string $email,
        public readonly string $accessToken,
        public readonly ?string $refreshToken,
        public readonly ?string $avatarUrl,
    ) {}

    /**
     * Convert the DTO to an array format for API responses.
     *
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'provider_user_id' => $this->providerUserId,
            'name' => $this->name,
            'email' => $this->email,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'avatar_url' => $this->avatarUrl,
        ];
    }
}
