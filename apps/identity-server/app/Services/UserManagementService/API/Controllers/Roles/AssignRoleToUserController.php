<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\AssignRoleToUserRequest;
use App\Services\UserManagementService\API\Resources\Roles\RoleResource;
use App\Services\UserManagementService\Application\DTOs\Input\AssignRoleToUserDTO;
use App\Services\UserManagementService\Application\Services\Roles\AssignRoleToUserService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class AssignRoleToUserController extends Controller
{
    #[Route(path: 'roles/{role}/users/{user}', methods: ['POST'], middleware: ['api', 'auth:sanctum'], name: 'roles.users.assign')]
    #[Request(AssignRoleToUserRequest::class, AssignRoleToUserDTO::class)]
    #[Service(AssignRoleToUserService::class, 'Role assigned to user successfully.')]
    #[Response(RoleResource::class)]
    #[Doc(
        summary: 'Assign role to user',
        description: 'Attach a role to a specific user.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
