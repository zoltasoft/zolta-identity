<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Users;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

/**
 * @internal Convey a single User aggregate through CQRS handlers.
 */
final readonly class UserPayload implements MessagePayloadInterface
{
    public function __construct(private User $user) {}

    public function user(): User
    {
        return $this->user;
    }

    public function toArray(): array
    {
        return ['user' => $this->user];
    }
}
