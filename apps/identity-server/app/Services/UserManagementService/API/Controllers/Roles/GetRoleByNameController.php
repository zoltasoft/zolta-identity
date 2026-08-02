<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\GetRoleByNameRequest;
use App\Services\UserManagementService\API\Resources\Roles\RoleResource;
use App\Services\UserManagementService\Application\DTOs\Input\GetRoleByNameDTO;
use App\Services\UserManagementService\Application\Services\Roles\GetRoleByNameService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class GetRoleByNameController extends Controller
{
    #[Route(path: 'roles/by-name/{name}', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'roles.by_name')]
    #[Request(GetRoleByNameRequest::class, GetRoleByNameDTO::class)]
    #[Service(GetRoleByNameService::class, 'Role retrieved successfully.')]
    #[Response(RoleResource::class)]
    #[Doc(
        summary: 'Get role by name',
        description: 'Retrieve a role definition by its unique name.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
