<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\API\Requests\Identity\RemoveIdentityHostedApplicationLogoRequest;
use App\Services\UserManagementService\API\Resources\RouteActions\RemoveIdentityHostedApplicationLogoResponse;
use App\Services\UserManagementService\Application\DTOs\Input\RemoveIdentityHostedApplicationLogoDTO;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Service;

#[Route('v1/identity/projects/{project}/hosted-applications/{hosted_application}/logo', methods: ['DELETE'], middleware: ['api', 'auth:sanctum', 'identity.token'], name: 'identity.projects.hosted_applications.logo.destroy')]
#[Request(RemoveIdentityHostedApplicationLogoRequest::class, RemoveIdentityHostedApplicationLogoDTO::class)]
#[Service(RemoveIdentityHostedApplicationLogoResponse::class)]
final class RemoveIdentityHostedApplicationLogoController extends Controller {}
