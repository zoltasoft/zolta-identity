<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Identity;

use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class IdentityOperationPayload implements MessagePayloadInterface
{
    public function __construct(private mixed $result) {}

    /** @return array{result: mixed} */
    public function toArray(): array
    {
        return ['result' => $this->result];
    }
}
