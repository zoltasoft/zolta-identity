<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\GetUserByEmailRequest;
use App\Services\UserManagementService\API\Resources\Users\GetUserByEmailResource;
use App\Services\UserManagementService\Application\DTOs\Input\GetUserByEmailDTO;
use App\Services\UserManagementService\Application\Services\Users\GetUserByEmailService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('users/by-email/{email}', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'users.getByEmail')]
#[Request(GetUserByEmailRequest::class, GetUserByEmailDTO::class)]
#[Service(GetUserByEmailService::class, 'Request resolved successfully.', 200)]
#[Response(GetUserByEmailResource::class)]
#[Doc(summary: 'Get user by email', description: 'Gets the user informations.', tags: ['Users'])]
class GetUserByEmailController extends Controller {}
