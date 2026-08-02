<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\LogoutRequest;
use App\Services\UserManagementService\API\Resources\Authentication\LogoutResource;
use App\Services\UserManagementService\Application\Services\Authentication\LogoutService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/logout', methods: ['POST'], middleware: ['api', 'auth:sanctum'], name: 'auth.logout')]
#[Request(LogoutRequest::class)]
#[Service(LogoutService::class, 'Successfully logged out.')]
#[Response(LogoutResource::class)]
#[Doc(
    summary: 'Logout',
    description: 'Invalidate the current access token for the authenticated user.',
    tags: ['Authentication']
)]
final class LogoutController extends Controller {}
