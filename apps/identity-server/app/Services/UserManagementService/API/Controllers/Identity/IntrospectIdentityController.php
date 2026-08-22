<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\API\Requests\Identity\IdentityAuthenticationOperationRequest;
use App\Services\UserManagementService\API\Resources\RouteActions\IntrospectIdentityResponse;
use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Service;

#[Route('v1/identity/auth/introspect', methods: ['POST'], middleware: ['api', 'throttle:300,1'], name: 'identity.auth.introspect')]
#[Request(IdentityAuthenticationOperationRequest::class, IdentityOperationDTO::class)]
#[Service(IntrospectIdentityResponse::class)]
final class IntrospectIdentityController extends Controller {}
