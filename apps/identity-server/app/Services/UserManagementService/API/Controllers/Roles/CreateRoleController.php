<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\CreateRoleRequest;
use App\Services\UserManagementService\API\Resources\Roles\RoleResource;
use App\Services\UserManagementService\Application\DTOs\Input\CreateRoleDTO;
use App\Services\UserManagementService\Application\Services\Roles\CreateRoleService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class CreateRoleController extends Controller
{
    #[Route(path: 'roles', methods: ['POST'], middleware: ['api', 'auth:sanctum'], name: 'roles.store')]
    #[Request(CreateRoleRequest::class, CreateRoleDTO::class)]
    #[Service(CreateRoleService::class, 'Role created successfully.', 201)]
    #[Response(RoleResource::class)]
    #[Doc(
        summary: 'Create role',
        description: 'Create a new role and optionally attach permissions.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
