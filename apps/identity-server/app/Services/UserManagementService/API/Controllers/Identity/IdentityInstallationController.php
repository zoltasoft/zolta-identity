<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\API\Requests\Identity\IdentityInstallationOperationRequest;
use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use App\Services\UserManagementService\Application\Services\Identity\ExecuteIdentityInstallationService;
use App\Services\UserManagementService\Application\Services\Identity\ReadIdentityInstallationService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Service;

final class IdentityInstallationController extends Controller
{
    #[Route('v1/identity/users', methods: ['GET'], middleware: ['api', 'auth:sanctum', 'identity.token'], name: 'identity.installation.users.index')]
    #[Request(IdentityInstallationOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ReadIdentityInstallationService::class, 'Installation users retrieved.')]
    public function users(): void {}

    #[Route('v1/identity/users/{user}', methods: ['PATCH'], middleware: ['api', 'auth:sanctum', 'identity.token'], name: 'identity.installation.users.update')]
    #[Request(IdentityInstallationOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityInstallationService::class, 'Installation user updated.')]
    public function updateUser(): void {}
}
