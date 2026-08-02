<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\AssignPermissionToRoleRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\AssignPermissionToRoleDTO;
use App\Services\UserManagementService\Application\Services\Permissions\AssignPermissionToRoleService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(
    path: 'permissions/{permission}/roles/{role}',
    methods: ['POST'],
    middleware: ['api', 'auth:sanctum'],
    name: 'permissions.roles.assign'
)]
#[Request(AssignPermissionToRoleRequest::class, AssignPermissionToRoleDTO::class)]
#[Service(AssignPermissionToRoleService::class, 'Permission assigned to role successfully.', 200)]
#[Response(PermissionResource::class)]
#[Doc(
    summary: 'Assign permission to a role',
    description: 'Associates a permission to a role.',
    tags: ['Permissions', 'Roles']
)]
final class AssignPermissionToRoleController extends Controller {}
