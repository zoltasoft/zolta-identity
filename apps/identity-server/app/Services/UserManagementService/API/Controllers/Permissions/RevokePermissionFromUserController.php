<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\RevokePermissionFromUserRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\RevokePermissionFromUserDTO;
use App\Services\UserManagementService\Application\Services\Permissions\RevokePermissionFromUserService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(
    path: 'permissions/{permission}/users/{user}',
    methods: ['DELETE'],
    middleware: ['api', 'auth:sanctum'],
    name: 'permissions.users.revoke'
)]
#[Request(RevokePermissionFromUserRequest::class, RevokePermissionFromUserDTO::class)]
#[Service(RevokePermissionFromUserService::class, 'Permission revoked from user successfully.')]
#[Response(PermissionResource::class)]
#[Doc(
    summary: 'Revoke permission from user',
    description: 'Detach a permission from a user account.',
    tags: ['Permissions', 'Users']
)]
final class RevokePermissionFromUserController extends Controller {}
