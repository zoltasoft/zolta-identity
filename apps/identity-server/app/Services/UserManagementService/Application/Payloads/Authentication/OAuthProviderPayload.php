<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Authentication;

use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class OAuthProviderPayload implements MessagePayloadInterface
{
    public function __construct(private OAuthProvider $oAuthProvider) {}

    public function provider(): OAuthProvider
    {
        return $this->oAuthProvider;
    }

    public function toArray(): array
    {
        return ['provider' => $this->oAuthProvider];
    }
}
