<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Resources\RouteActions;

use App\Services\UserManagementService\Application\DTOs\Input\UploadIdentityHostedApplicationLogoDTO;
use App\Services\UserManagementService\Application\Services\Identity\UploadIdentityHostedApplicationLogoService;
use Illuminate\Http\JsonResponse;

final readonly class UploadIdentityHostedApplicationLogoResponse
{
    public function __construct(private UploadIdentityHostedApplicationLogoService $uploadLogo) {}

    public function __invoke(UploadIdentityHostedApplicationLogoDTO $dto): JsonResponse
    {
        return response()->json(['data' => ($this->uploadLogo)($dto)], 201);
    }
}
