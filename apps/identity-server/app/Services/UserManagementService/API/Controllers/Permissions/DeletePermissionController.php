<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\DeletePermissionRequest;
use App\Services\UserManagementService\API\Resources\Permissions\DeletePermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\DeletePermissionDTO;
use App\Services\UserManagementService\Application\Services\Permissions\DeletePermissionService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'permissions/{id}', methods: ['DELETE'], middleware: ['api', 'auth:sanctum'], name: 'permissions.destroy')]
#[Request(DeletePermissionRequest::class, DeletePermissionDTO::class)]
#[Service(DeletePermissionService::class, 'Permission deleted successfully.')]
#[Response(DeletePermissionResource::class)]
#[Doc(
    summary: 'Delete permission',
    description: 'Remove a permission from the system.',
    tags: ['Permissions']
)]
final class DeletePermissionController extends Controller {}
