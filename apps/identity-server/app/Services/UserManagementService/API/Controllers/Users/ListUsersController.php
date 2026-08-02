<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\ListUsersRequest;
use App\Services\UserManagementService\API\Resources\Users\UserCollectionResource;
use App\Services\UserManagementService\Application\DTOs\Input\ListUsersDTO;
use App\Services\UserManagementService\Application\Services\Users\ListUsersService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'users', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'users.index')]
#[Request(ListUsersRequest::class, ListUsersDTO::class)]
#[Service(ListUsersService::class, 'Users retrieved successfully.', 200)]
#[Response(UserCollectionResource::class)]
#[Doc(
    summary: 'List Users',
    description: 'Retrieve all available users with their permissions and roles.',
    tags: ['Users']
)]
final class ListUsersController extends Controller {}
