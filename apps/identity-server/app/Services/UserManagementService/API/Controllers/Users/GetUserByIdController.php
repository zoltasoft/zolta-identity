<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\GetUserByIdRequest;
use App\Services\UserManagementService\API\Resources\Users\GetUserByIdResource;
use App\Services\UserManagementService\Application\DTOs\Input\GetUserByIdDTO;
use App\Services\UserManagementService\Application\Services\Users\GetUserByIdService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('users/{id}', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'users.getById')]
#[Request(GetUserByIdRequest::class, GetUserByIdDTO::class)]
#[Service(GetUserByIdService::class, 'Request resolved successfully.', 200)]
#[Response(GetUserByIdResource::class)]
#[Doc(summary: 'Get user by id', description: 'Gets the user informations.', tags: ['Users'])]
class GetUserByIdController extends Controller {}
