<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Events;

use DateTimeImmutable;
use Zolta\Domain\Events\Contracts\EventInterface;
use Zolta\Domain\ValueObjects\UserId;

final readonly class PasswordChanged implements EventInterface
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        private UserId $userId,
        private string $reason,
    ) {
        $this->occurredOn = new DateTimeImmutable;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
