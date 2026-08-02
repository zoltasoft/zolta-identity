<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\RefreshTokenRequest;
use App\Services\UserManagementService\API\Resources\Authentication\RefreshResource;
use App\Services\UserManagementService\Application\DTOs\Input\RefreshDTO;
use App\Services\UserManagementService\Application\Services\Authentication\RefreshTokenService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/refresh', methods: ['POST'], middleware: ['api', 'auth:sanctum'], name: 'auth.refresh')]
#[Request(RefreshTokenRequest::class, RefreshDTO::class)]
#[Service(RefreshTokenService::class, 'Token refreshed successfully.')]
#[Response(RefreshResource::class)]
#[Doc(
    summary: 'Refresh token',
    description: 'Issue a new access token for the authenticated user.',
    tags: ['Authentication']
)]
final class RefreshTokenController extends Controller {}
