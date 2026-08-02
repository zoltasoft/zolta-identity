<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\ProvisionUserAccessRequest;
use App\Services\UserManagementService\API\Resources\Users\ProvisionedUserAccessResource;
use App\Services\UserManagementService\Application\DTOs\Input\ProvisionUserAccessDTO;
use App\Services\UserManagementService\Application\Services\Users\ProvisionUserAccessService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('users/provision-access', methods: ['POST'], middleware: ['api', 'auth:sanctum'], name: 'users.provision-access')]
#[Request(ProvisionUserAccessRequest::class, ProvisionUserAccessDTO::class)]
#[Service(ProvisionUserAccessService::class, 'User access provisioned successfully.')]
#[Response(ProvisionedUserAccessResource::class)]
#[Doc(
    summary: 'Provision user access',
    description: 'Assign a role and a set of permissions to a user in one composite operation.',
    tags: ['Users', 'Permissions', 'Roles']
)]
final class ProvisionUserAccessController extends Controller {}
