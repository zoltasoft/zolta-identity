<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\AssignPermissionToUserRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\AssignPermissionToUserDTO;
use App\Services\UserManagementService\Application\Services\Permissions\AssignPermissionToUserService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(
    path: 'permissions/{permission}/users/{user}',
    methods: ['POST'],
    middleware: ['api', 'auth:sanctum'],
    name: 'permissions.users.assign'
)]
#[Request(AssignPermissionToUserRequest::class, AssignPermissionToUserDTO::class)]
#[Service(AssignPermissionToUserService::class, 'Permission assigned to user successfully.', 200)]
#[Response(PermissionResource::class)]
#[Doc(
    summary: 'Assign permission to user',
    description: 'Associates a permission with a user account.',
    tags: ['Permissions']
)]
final class AssignPermissionToUserController extends Controller {}
