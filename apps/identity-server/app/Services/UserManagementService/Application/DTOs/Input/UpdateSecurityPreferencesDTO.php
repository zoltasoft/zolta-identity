<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class UpdateSecurityPreferencesDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('user_id')]
        public readonly string $userId,
        #[FromRequest('two_factor_enabled')]
        public readonly bool $twoFactorEnabled,
        #[FromRequest('login_alerts_enabled')]
        public readonly bool $loginAlertsEnabled,
        #[FromRequest('backup_email')]
        public readonly ?string $backupEmail = null,
    ) {}
}
