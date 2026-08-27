<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class GetUserByEmailResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $user,
        public readonly array $captured = [],
    ) {}

    public static function fromDomain(User $user, array $captureLog = []): self
    {
        $payload = [
            'id' => $user->getId()->get('value'),
            'email' => $user->getEmail()->get('address'),
            'username' => $user->getUsername()->get('username'),
            'terms' => $user->getTerms()->value,
        ];

        return new self($payload, $captureLog);
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'captured' => $this->captured,
        ];
    }
}
