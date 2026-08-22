<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\User;
use App\Services\UserManagementService\Domain\Entities\OAuthAccount;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class UserCollectionResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $users,
        public readonly array $meta = [],
        public readonly array $captured = [],
    ) {}

    /**
     * @param  User[]  $users
     */
    public static function fromDomain(array $users, array $meta = [], array $captureLog = []): self
    {
        $normalizedUsers = array_map(static function (User $user): array {
            $oauthAccounts = array_map(
                static function (OAuthAccount $oAuthAccount): array {
                    $providerName = $oAuthAccount->getProviderName();

                    return [
                        'id' => $oAuthAccount->getId()->get('value'),
                        'provider_id' => $oAuthAccount->getOAuthProviderId()->get('value'),
                        'provider_user_id' => $oAuthAccount->getOAuthProviderUserId(),
                        'provider' => $providerName,
                        'provider_name' => $providerName,
                        'linked_at' => $oAuthAccount->getCreatedAt()->format(DATE_ATOM),
                    ];
                },
                $user->getOAuthAccounts()
            );

            $hasSocialAccounts = count($oauthAccounts) > 0;
            $socialProviders = array_values(array_unique(array_filter(array_map(
                static fn (array $account): mixed => $account['provider'] ?? $account['provider_id'] ?? null,
                $oauthAccounts
            ), static fn ($value): bool => is_string($value) && $value !== '')));

            return [
                'id' => $user->getId()->get('value'),
                'email' => $user->getEmail()->get('address'),
                'username' => $user->getUsername()->get('username'),
                'status' => $user->isLocked() ? 'Locked' : 'Active',
                'created_at' => $user->getCreatedAt()->format(DATE_ATOM),
                'updated_at' => $user->getUpdatedAt()->format(DATE_ATOM),
                'oauth_accounts' => $oauthAccounts,
                'has_social_accounts' => $hasSocialAccounts,
                'social_providers' => $socialProviders,
            ];
        }, $users);

        $metaPayload = array_merge([
            'count' => count($users),
        ], $meta);

        return new self($normalizedUsers, $metaPayload, $captureLog);
    }

    public function toArray(): array
    {
        return [
            'users' => $this->users,
            'meta' => $this->meta,
            'captured' => $this->captured,
        ];
    }
}
