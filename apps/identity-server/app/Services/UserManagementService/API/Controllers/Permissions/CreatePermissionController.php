<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\CreatePermissionRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\CreatePermissionDTO;
use App\Services\UserManagementService\Application\Services\Permissions\CreatePermissionService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'permissions', methods: ['POST'], middleware: ['api', 'auth:sanctum'], name: 'permissions.store')]
#[Request(CreatePermissionRequest::class, CreatePermissionDTO::class)]
#[Service(CreatePermissionService::class, 'Permission created successfully.', 201)]
#[Response(PermissionResource::class)]
#[Doc(
    summary: 'Create permission',
    description: 'Create a new permission entry.',
    tags: ['Permissions']
)]
final class CreatePermissionController extends Controller {}
