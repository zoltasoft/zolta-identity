<?php

namespace App\Services\UserManagementService\Infrastructure\Events;

use App\Services\UserManagementService\Domain\Events\UserRegisteredEvent as DomainUserRegistered;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Zolta\Cqrs\Events\Attributes\HandlesDomainEvent;
use Zolta\Domain\Events\Contracts\EventInterface;

/**
 * An infrastructure-specific event that wraps the UserRegistered domain event.
 *
 * This class is used by the Laravel event system and acts as a lightweight
 * data transfer object to move the pure domain event to the appropriate listeners.
 */
#[HandlesDomainEvent(DomainUserRegistered::class)]
class UserRegisteredEvent implements EventInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly DomainUserRegistered $domainEvent
    ) {}

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->domainEvent->occurredOn();
    }
}
