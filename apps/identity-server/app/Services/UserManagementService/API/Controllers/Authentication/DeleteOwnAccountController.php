<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\DeleteOwnAccountRequest;
use App\Services\UserManagementService\API\Resources\Users\DeleteUserByIdResource;
use App\Services\UserManagementService\Application\DTOs\Input\DeleteUserByIdDTO;
use App\Services\UserManagementService\Application\Services\Users\DeleteUserByIdService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/account', methods: ['DELETE'], middleware: ['api', 'auth:sanctum', 'throttle:3,1'], name: 'auth.account.delete')]
#[Request(DeleteOwnAccountRequest::class, DeleteUserByIdDTO::class)]
#[Service(DeleteUserByIdService::class, 'Account deleted.', 200)]
#[Response(DeleteUserByIdResource::class)]
#[Doc(summary: 'Delete own account', description: 'Permanently delete the authenticated user account.', tags: ['Authentication'])]
final class DeleteOwnAccountController extends Controller {}
