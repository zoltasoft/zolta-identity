<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\API\Requests\Identity\UploadIdentityHostedApplicationLogoRequest;
use App\Services\UserManagementService\API\Resources\RouteActions\UploadIdentityHostedApplicationLogoResponse;
use App\Services\UserManagementService\Application\DTOs\Input\UploadIdentityHostedApplicationLogoDTO;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Service;

#[Route('v1/identity/projects/{project}/hosted-applications/{hosted_application}/logo', methods: ['POST'], middleware: ['api', 'auth:sanctum', 'identity.token'], name: 'identity.projects.hosted_applications.logo.store')]
#[Request(UploadIdentityHostedApplicationLogoRequest::class, UploadIdentityHostedApplicationLogoDTO::class)]
#[Service(UploadIdentityHostedApplicationLogoResponse::class)]
final class UploadIdentityHostedApplicationLogoController extends Controller {}
