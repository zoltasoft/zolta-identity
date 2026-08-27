<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Authentication;

use App\Services\UserManagementService\Application\DTOs\External\OAuthUser;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;

final readonly class OAuthRemoteUserPayload implements MessagePayloadInterface
{
    public function __construct(private OAuthUser $oauthUser) {}

    public function oauthUser(): OAuthUser
    {
        return $this->oauthUser;
    }

    public function toArray(): array
    {
        return ['oauthUser' => $this->oauthUser];
    }
}
