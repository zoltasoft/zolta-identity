<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\RevokePermissionFromRoleRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\RevokePermissionFromRoleDTO;
use App\Services\UserManagementService\Application\Services\Permissions\RevokePermissionFromRoleService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(
    path: 'permissions/{permission}/roles/{role}',
    methods: ['DELETE'],
    middleware: ['api', 'auth:sanctum'],
    name: 'permissions.roles.revoke'
)]
#[Request(RevokePermissionFromRoleRequest::class, RevokePermissionFromRoleDTO::class)]
#[Service(RevokePermissionFromRoleService::class, 'Permission revoked from role successfully.')]
#[Response(PermissionResource::class)]
#[Doc(
    summary: 'Revoke permission from role',
    description: 'Detach a permission from a role.',
    tags: ['Permissions', 'Roles']
)]
final class RevokePermissionFromRoleController extends Controller {}
