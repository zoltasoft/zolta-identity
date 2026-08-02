<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Authentication;

use App\Services\UserManagementService\Domain\Entities\OAuthAccount;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class OAuthAccountPayload implements MessagePayloadInterface
{
    public function __construct(private OAuthAccount $oAuthAccount) {}

    public function account(): OAuthAccount
    {
        return $this->oAuthAccount;
    }

    public function toArray(): array
    {
        return ['account' => $this->oAuthAccount];
    }
}
