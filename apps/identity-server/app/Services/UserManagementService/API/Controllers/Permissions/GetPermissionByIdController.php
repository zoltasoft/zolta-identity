<?php

namespace App\Services\UserManagementService\API\Controllers\Permissions;

use App\Services\UserManagementService\API\Requests\Permissions\GetPermissionByIdRequest;
use App\Services\UserManagementService\API\Resources\Permissions\PermissionResource;
use App\Services\UserManagementService\Application\DTOs\Input\GetPermissionByIdDTO;
use App\Services\UserManagementService\Application\Services\Permissions\GetPermissionByIdService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'permissions/{id}', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'permissions.show')]
#[Request(GetPermissionByIdRequest::class, GetPermissionByIdDTO::class)]
#[Service(GetPermissionByIdService::class, 'Permission retrieved successfully.')]
#[Response(PermissionResource::class)]
#[Doc(
    summary: 'Get permission',
    description: 'Fetch a permission by its identifier.',
    tags: ['Permissions']
)]
final class GetPermissionByIdController extends Controller {}
