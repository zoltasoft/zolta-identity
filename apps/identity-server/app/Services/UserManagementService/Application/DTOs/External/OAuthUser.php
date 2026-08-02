<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\External;

/**
 * Data Transfer Object representing an social user.
 */
final readonly class OAuthUser
{
    /**
     * @param  string  $providerUserId  The social user's id.
     * @param  string  $name  The social user's name.
     * @param  string  $email  The social user's email address.
     * @param  string  $accessToken  The social user's access_token.
     * @param  string|null  $refreshToken  The social user's refresh_token (may be null for some providers).
     * @param  string|null  $avatarUrl  The social user's avatar_url.
     */
    public function __construct(
        public string $providerUserId,
        public string $name,
        public string $email,
        public string $accessToken,
        public ?string $refreshToken,
        public ?string $avatarUrl,
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
