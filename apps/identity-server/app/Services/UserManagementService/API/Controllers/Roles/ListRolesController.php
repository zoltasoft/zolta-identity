<?php

namespace App\Services\UserManagementService\API\Controllers\Roles;

use App\Services\UserManagementService\API\Requests\Roles\ListRolesRequest;
use App\Services\UserManagementService\API\Resources\Roles\RoleCollectionResource;
use App\Services\UserManagementService\Application\DTOs\Input\ListRolesDTO;
use App\Services\UserManagementService\Application\Services\Roles\ListRolesService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

final class ListRolesController extends Controller
{
    #[Route(path: 'roles', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'roles.index')]
    #[Request(ListRolesRequest::class, ListRolesDTO::class)]
    #[Service(ListRolesService::class, 'Roles retrieved successfully.')]
    #[Response(RoleCollectionResource::class)]
    #[Doc(
        summary: 'List roles',
        description: 'Retrieve all available roles with their permissions.',
        tags: ['Roles']
    )]
    public function __invoke(): void {}
}
