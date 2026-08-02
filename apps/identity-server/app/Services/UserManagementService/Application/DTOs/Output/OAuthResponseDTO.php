<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Application\DTOs\External\AuthenticatedUser;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class OAuthResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly ?AuthenticatedUser $user = null,
    ) {}
}
