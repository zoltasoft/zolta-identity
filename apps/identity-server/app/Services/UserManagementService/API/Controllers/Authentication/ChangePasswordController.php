<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\ChangePasswordRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AuthenticationMessageResource;
use App\Services\UserManagementService\Application\DTOs\Input\ChangePasswordDTO;
use App\Services\UserManagementService\Application\Services\Authentication\ChangePasswordService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/password', methods: ['PUT'], middleware: ['api', 'auth:sanctum', 'throttle:6,1'], name: 'auth.password.change')]
#[Request(ChangePasswordRequest::class, ChangePasswordDTO::class)]
#[Service(ChangePasswordService::class, 'Password updated.', 200)]
#[Response(AuthenticationMessageResource::class)]
#[Doc(summary: 'Change password', description: 'Change the authenticated user password after confirming the current password.', tags: ['Authentication'])]
final class ChangePasswordController extends Controller {}
