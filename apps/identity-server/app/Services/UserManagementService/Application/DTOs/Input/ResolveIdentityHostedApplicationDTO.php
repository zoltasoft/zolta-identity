<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class ResolveIdentityHostedApplicationDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('application')]
        public readonly string $application,
        #[FromRequest('by_client')]
        public readonly bool $byClient,
    ) {}
}
