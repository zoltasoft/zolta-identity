<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\DeleteRoleRequest;
use App\Services\UserManagementService\API\Resources\Roles\DeleteRoleResource;
use App\Services\UserManagementService\Application\DTOs\Input\DeleteRoleDTO;
use App\Services\UserManagementService\Application\Services\Roles\DeleteRoleService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class DeleteRoleController extends Controller
{
    #[Route(path: 'roles/{id}', methods: ['DELETE'], middleware: ['api', 'auth:sanctum'], name: 'roles.destroy')]
    #[Request(DeleteRoleRequest::class, DeleteRoleDTO::class)]
    #[Service(DeleteRoleService::class, 'Role deleted successfully.')]
    #[Response(DeleteRoleResource::class)]
    #[Doc(
        summary: 'Delete role',
        description: 'Delete a role that is not system-owned and not assigned to users.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
