<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class LoginResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly string $access_token,
        public readonly string $access_token_expires_at,
        public readonly array $user
    ) {}

    public static function fromDomain(User $user, AccessToken $accessToken): static
    {
        return new self(
            access_token: $accessToken->get('token'),
            access_token_expires_at: $accessToken->get('expiresAt')->format(DATE_ATOM),
            user: [
                'email' => $user->getEmail()->address,
                'email_verified_at' => $user->getEmail()->get('verifiedAt')?->format(DATE_ATOM),
                'username' => $user->getUsername()->username,
                'id' => $user->getId()->get(),
                'is_temporary' => $user->isTemporary(),
                'demo_expires_at' => $user->getDemoExpiresAt()?->format(DATE_ATOM),
            ],
        );
    }
}
