<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\LoginRequest;
use App\Services\UserManagementService\API\Resources\Authentication\LoginResource;
use App\Services\UserManagementService\Application\DTOs\Input\LoginDTO;
use App\Services\UserManagementService\Application\Services\Authentication\LoginService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('auth/login', methods: ['POST'], middleware: ['api'], name: 'auth.login')]
#[Request(LoginRequest::class, LoginDTO::class)]
#[Service(LoginService::class, 'Login successful.', 200)]
#[Response(LoginResource::class)]
#[Doc(summary: 'User login', description: 'Authenticates a user and returns a JWT + basic info.', tags: ['Authentication'])]
final class LoginController extends Controller {}
