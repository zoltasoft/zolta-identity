<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Listeners;

use App\Services\UserManagementService\Application\Contracts\SendWelcomeMessageServiceInterface;
use App\Services\UserManagementService\Infrastructure\Events\UserRegisteredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendWelcomeEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(private SendWelcomeMessageServiceInterface $service) {}

    public function handle(UserRegisteredEvent $event): void
    {
        $this->service->handleEvent($event->domainEvent);
    }
}
