<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\UpdatePermissionRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\UpdatePermissionDTO;
use App\Services\UserManagementService\Application\Services\Permissions\UpdatePermissionService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'permissions/{id}', methods: ['PUT'], middleware: ['api', 'auth:sanctum'], name: 'permissions.update')]
#[Request(UpdatePermissionRequest::class, UpdatePermissionDTO::class)]
#[Service(UpdatePermissionService::class, 'Permission updated successfully.')]
#[Response(PermissionResource::class)]
#[Doc(
    summary: 'Update permission',
    description: 'Update an existing permission.',
    tags: ['Permissions']
)]
final class UpdatePermissionController extends Controller {}
