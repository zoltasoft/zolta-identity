<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\RegisterRequest;
use App\Services\UserManagementService\API\Resources\Authentication\RegisterResource;
use App\Services\UserManagementService\Application\DTOs\Input\RegisterDTO;
use App\Services\UserManagementService\Application\Services\Authentication\RegisterService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('auth/register', methods: ['POST'], middleware: ['api'], name: 'auth.register')]
#[Request(RegisterRequest::class, RegisterDTO::class)]
#[Service(RegisterService::class, 'Register successful.', 200)]
#[Response(RegisterResource::class)]
#[Doc(summary: 'User register', description: 'Registers a user and returns a JWT + basic info.', tags: ['Authentication'])]
class RegisterController extends Controller {}
