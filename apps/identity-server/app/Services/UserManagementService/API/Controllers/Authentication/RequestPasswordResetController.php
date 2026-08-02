<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\RequestPasswordResetRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AuthenticationMessageResource;
use App\Services\UserManagementService\Application\DTOs\Input\RequestPasswordResetDTO;
use App\Services\UserManagementService\Application\Services\Authentication\RequestPasswordResetService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/password/forgot', methods: ['POST'], middleware: ['api', 'throttle:6,1'], name: 'auth.password.forgot')]
#[Request(RequestPasswordResetRequest::class, RequestPasswordResetDTO::class)]
#[Service(RequestPasswordResetService::class, 'Password reset request accepted.', 200)]
#[Response(AuthenticationMessageResource::class)]
#[Doc(summary: 'Request password reset', description: 'Send a password reset link without exposing account existence.', tags: ['Authentication'])]
final class RequestPasswordResetController extends Controller {}
