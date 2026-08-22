<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Resources\RouteActions;

use App\Services\UserManagementService\Application\DTOs\Input\RemoveIdentityHostedApplicationLogoDTO;
use App\Services\UserManagementService\Application\Services\Identity\RemoveIdentityHostedApplicationLogoService;
use Illuminate\Http\JsonResponse;

final readonly class RemoveIdentityHostedApplicationLogoResponse
{
    public function __construct(private RemoveIdentityHostedApplicationLogoService $removeLogo) {}

    public function __invoke(RemoveIdentityHostedApplicationLogoDTO $dto): JsonResponse
    {
        return response()->json(['data' => ($this->removeLogo)($dto)]);
    }
}
