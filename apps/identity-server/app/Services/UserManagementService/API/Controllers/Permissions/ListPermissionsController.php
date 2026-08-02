<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\ListPermissionsRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionCollectionResource;
use App\Services\UserManagementService\Application\DTOs\Input\ListPermissionsDTO;
use App\Services\UserManagementService\Application\Services\Permissions\ListPermissionsService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'permissions', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'permissions.index')]
#[Request(ListPermissionsRequest::class, ListPermissionsDTO::class)]
#[Service(ListPermissionsService::class, 'Permissions retrieved successfully.')]
#[Response(PermissionCollectionResource::class)]
#[Doc(
    summary: 'List permissions',
    description: 'Retrieve all permissions with optional filtering and includes.',
    tags: ['Permissions']
)]
final class ListPermissionsController extends Controller {}
