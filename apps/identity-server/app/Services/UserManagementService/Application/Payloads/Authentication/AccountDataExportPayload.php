<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Authentication;

use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class AccountDataExportPayload implements MessagePayloadInterface
{
    public function __construct(private array $export) {}

    public function toArray(): array
    {
        return ['accountExport' => $this->export];
    }
}
