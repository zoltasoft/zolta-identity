<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class SecurityPreferencesResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly bool $twoFactorEnabled,
        public readonly bool $loginAlertsEnabled,
        public readonly ?string $backupEmail,
    ) {}

    /**
     * @param  array{user_id:string,two_factor_enabled:bool,login_alerts_enabled:bool,backup_email:?string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['user_id'],
            $payload['two_factor_enabled'],
            $payload['login_alerts_enabled'],
            $payload['backup_email'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'two_factor_enabled' => $this->twoFactorEnabled,
            'login_alerts_enabled' => $this->loginAlertsEnabled,
            'backup_email' => $this->backupEmail,
        ];
    }
}
