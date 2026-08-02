<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\DeleteUserByEmailRequest;
use App\Services\UserManagementService\API\Resources\Users\DeleteUserByEmailResource;
use App\Services\UserManagementService\Application\DTOs\Input\DeleteUserByEmailDTO;
use App\Services\UserManagementService\Application\Services\Users\DeleteUserByEmailService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('users/by-email/{email}', methods: ['DELETE'], middleware: ['api', 'auth:sanctum'], name: 'users.deleteByEmail')]
#[Request(DeleteUserByEmailRequest::class, DeleteUserByEmailDTO::class)]
#[Service(DeleteUserByEmailService::class, 'User deleted successfully.')]
#[Response(DeleteUserByEmailResource::class)]
#[Doc(
    summary: 'Delete a user by its email',
    description: 'Delete user from the DB.',
    tags: ['Users']
)]
class DeleteUserByEmailController extends Controller {}
