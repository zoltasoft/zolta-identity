<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

use Zolta\Domain\Events\Contracts\EventInterface;

interface SendWelcomeMessageServiceInterface
{
    /**
     * Handle the given domain event.
     */
    public function handleEvent(EventInterface $event): void;
}
