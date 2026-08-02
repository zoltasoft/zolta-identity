<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Authentication;

use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class AccountSessionCollectionPayload implements MessagePayloadInterface
{
    public function __construct(private array $sessions) {}

    public function toArray(): array
    {
        return ['sessions' => $this->sessions];
    }
}
