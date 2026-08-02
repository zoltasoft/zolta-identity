<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\GetRoleByIdRequest;
use App\Services\UserManagementService\API\Resources\Roles\RoleResource;
use App\Services\UserManagementService\Application\DTOs\Input\GetRoleByIdDTO;
use App\Services\UserManagementService\Application\Services\Roles\GetRoleByIdService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class GetRoleByIdController extends Controller
{
    #[Route(path: 'roles/{id}', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'roles.show')]
    #[Request(GetRoleByIdRequest::class, GetRoleByIdDTO::class)]
    #[Service(GetRoleByIdService::class, 'Role retrieved successfully.')]
    #[Response(RoleResource::class)]
    #[Doc(
        summary: 'Get role by ID',
        description: 'Retrieve a role by its unique identifier.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
