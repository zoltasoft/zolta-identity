<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Resources\RouteActions;

use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use App\Services\UserManagementService\Application\Services\Identity\ReadIdentityAuthenticationService;
use Illuminate\Http\JsonResponse;

final readonly class IntrospectIdentityResponse
{
    public function __construct(private ReadIdentityAuthenticationService $readIdentity) {}

    public function __invoke(IdentityOperationDTO $dto): JsonResponse
    {
        return response()->json(($this->readIdentity)($dto));
    }
}
