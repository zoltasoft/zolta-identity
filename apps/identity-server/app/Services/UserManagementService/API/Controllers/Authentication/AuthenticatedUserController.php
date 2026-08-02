<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\AuthenticatedUserRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AuthUserResource;
use App\Services\UserManagementService\Application\DTOs\Input\RefreshDTO;
use App\Services\UserManagementService\Application\Services\Authentication\AuthenticatedUserService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/user', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'auth.user')]
#[Request(AuthenticatedUserRequest::class, RefreshDTO::class)]
#[Service(AuthenticatedUserService::class, 'Authenticated user retrieved successfully.')]
#[Response(AuthUserResource::class)]
#[Doc(summary: 'Authenticated user', description: 'Retrieve the profile of the currently authenticated user.', tags: ['Authentication'])]
final class AuthenticatedUserController extends Controller {}
