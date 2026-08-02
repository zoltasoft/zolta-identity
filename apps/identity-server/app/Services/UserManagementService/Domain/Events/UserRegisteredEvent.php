<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Events;

use Zolta\Domain\Events\Contracts\EventInterface;
use Zolta\Domain\ValueObjects\UserId;

final readonly class UserRegisteredEvent implements EventInterface
{
    private \DateTimeImmutable $occurredOn;

    public function __construct(
        private UserId $userId,
    ) {
        $this->occurredOn = new \DateTimeImmutable;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
