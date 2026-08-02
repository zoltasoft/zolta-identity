<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\UpdateUserEmailRequest;
use App\Services\UserManagementService\API\Resources\Users\UpdateUserEmailResource;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateUserEmailDTO;
use App\Services\UserManagementService\Application\Services\Users\UpdateUserEmailService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('users/{id}/email', methods: ['PATCH'], middleware: ['api', 'auth:sanctum'], name: 'users.UpdateEmail')]
#[Request(UpdateUserEmailRequest::class, UpdateUserEmailDTO::class)]
#[Service(UpdateUserEmailService::class, 'Request resolved successfully.', 200)]
#[Response(UpdateUserEmailResource::class)]
#[Doc(summary: 'update user email', description: 'Update the user informations.', tags: ['Users'])]
class UpdateUserEmailController extends Controller {}
