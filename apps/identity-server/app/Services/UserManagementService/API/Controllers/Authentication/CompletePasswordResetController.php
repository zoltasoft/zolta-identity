<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\CompletePasswordResetRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AuthenticationMessageResource;
use App\Services\UserManagementService\Application\DTOs\Input\CompletePasswordResetDTO;
use App\Services\UserManagementService\Application\Services\Authentication\CompletePasswordResetService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/password/reset', methods: ['POST'], middleware: ['api', 'throttle:6,1'], name: 'auth.password.reset')]
#[Request(CompletePasswordResetRequest::class, CompletePasswordResetDTO::class)]
#[Service(CompletePasswordResetService::class, 'Password reset successful.', 200)]
#[Response(AuthenticationMessageResource::class)]
#[Doc(summary: 'Complete password reset', description: 'Set a new password using a valid reset token.', tags: ['Authentication'])]
final class CompletePasswordResetController extends Controller {}
