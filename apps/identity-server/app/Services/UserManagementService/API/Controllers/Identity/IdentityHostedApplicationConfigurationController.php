<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\API\Requests\Identity\IdentityHostedApplicationConfigurationRequest;
use App\Services\UserManagementService\Application\DTOs\Input\ResolveIdentityHostedApplicationDTO;
use App\Services\UserManagementService\Application\Services\Identity\ResolveIdentityHostedApplicationService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Service;

final class IdentityHostedApplicationConfigurationController extends Controller
{
    private const MIDDLEWARE = ['api', 'identity.hosted-internal', 'throttle:120,1'];

    #[Route('v1/identity/hosted-applications/{application}/configuration', methods: ['GET'], middleware: self::MIDDLEWARE, name: 'identity.hosted_applications.configuration')]
    #[Request(IdentityHostedApplicationConfigurationRequest::class, ResolveIdentityHostedApplicationDTO::class)]
    #[Service(ResolveIdentityHostedApplicationService::class, 'Hosted application configuration resolved.')]
    public function configuration(): void {}

    #[Route('v1/identity/hosted-clients/{application}/configuration', methods: ['GET'], middleware: self::MIDDLEWARE, name: 'identity.hosted_applications.client.configuration')]
    #[Request(IdentityHostedApplicationConfigurationRequest::class, ResolveIdentityHostedApplicationDTO::class)]
    #[Service(ResolveIdentityHostedApplicationService::class, 'Hosted application configuration resolved.')]
    public function clientConfiguration(): void {}
}
