<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\DeleteUserByIdRequest;
use App\Services\UserManagementService\API\Resources\Users\DeleteUserByIdResource;
use App\Services\UserManagementService\Application\DTOs\Input\DeleteUserByIdDTO;
use App\Services\UserManagementService\Application\Services\Users\DeleteUserByIdService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('users/{id}', methods: ['DELETE'], middleware: ['api', 'auth:sanctum'], name: 'users.deleteById')]
#[Request(DeleteUserByIdRequest::class, DeleteUserByIdDTO::class)]
#[Service(DeleteUserByIdService::class, 'User deleted successfully.')]
#[Response(DeleteUserByIdResource::class)]
#[Doc(
    summary: 'Delete a user by its id',
    description: 'Delete user from the DB.',
    tags: ['Users']
)]
class DeleteUserByIdController extends Controller {}
