<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\RevokeRoleFromUserRequest;
use App\Services\UserManagementService\API\Resources\Roles\RoleResource;
use App\Services\UserManagementService\Application\DTOs\Input\RevokeRoleFromUserDTO;
use App\Services\UserManagementService\Application\Services\Roles\RevokeRoleFromUserService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class RevokeRoleFromUserController extends Controller
{
    #[Route(path: 'roles/{role}/users/{user}', methods: ['DELETE'], middleware: ['api', 'auth:sanctum'], name: 'roles.users.revoke')]
    #[Request(RevokeRoleFromUserRequest::class, RevokeRoleFromUserDTO::class)]
    #[Service(RevokeRoleFromUserService::class, 'Role revoked from user successfully.')]
    #[Response(RoleResource::class)]
    #[Doc(
        summary: 'Revoke role from user',
        description: 'Detach a role from a specific user.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
