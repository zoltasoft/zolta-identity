<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\RevokeAccountSessionRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AuthenticationMessageResource;
use App\Services\UserManagementService\Application\DTOs\Input\RevokeAccountSessionDTO;
use App\Services\UserManagementService\Application\Services\Authentication\RevokeAccountSessionService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/sessions/{session}', methods: ['DELETE'], middleware: ['api', 'auth:sanctum', 'throttle:12,1'], name: 'auth.sessions.revoke')]
#[Request(RevokeAccountSessionRequest::class, RevokeAccountSessionDTO::class)]
#[Service(RevokeAccountSessionService::class, 'Session signed out.', 200)]
#[Response(AuthenticationMessageResource::class)]
#[Doc(summary: 'Sign out a session', description: 'Revoke one authentication session owned by the authenticated user.', tags: ['Authentication'])]
final class RevokeAccountSessionController extends Controller {}
