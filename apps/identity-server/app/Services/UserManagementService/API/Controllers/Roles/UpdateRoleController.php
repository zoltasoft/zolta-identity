<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\UpdateRoleRequest;
use App\Services\UserManagementService\API\Resources\Roles\RoleResource;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateRoleDTO;
use App\Services\UserManagementService\Application\Services\Roles\UpdateRoleService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class UpdateRoleController extends Controller
{
    #[Route(path: 'roles/{id}', methods: ['PUT'], middleware: ['api', 'auth:sanctum'], name: 'roles.update')]
    #[Request(UpdateRoleRequest::class, UpdateRoleDTO::class)]
    #[Service(UpdateRoleService::class, 'Role updated successfully.')]
    #[Response(RoleResource::class)]
    #[Doc(
        summary: 'Update role',
        description: 'Update role details and synchronize its permissions.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
