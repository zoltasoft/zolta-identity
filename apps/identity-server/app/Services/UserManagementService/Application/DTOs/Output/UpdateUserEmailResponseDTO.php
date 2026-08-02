<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class UpdateUserEmailResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $user,
        public readonly array $captured = [],
    ) {}

    public static function fromDomain(User $user, array $captured = []): self
    {
        $payload = [
            'id' => $user->getId()->get('value'),
            'email' => $user->getEmail()->get('address'),
            'username' => $user->getUsername()->get('username'),
            'email_verified_at' => $user->getEmail()->get('verifiedAt')?->format(DATE_ATOM),
        ];

        return new self($payload, $captured);
    }
}
