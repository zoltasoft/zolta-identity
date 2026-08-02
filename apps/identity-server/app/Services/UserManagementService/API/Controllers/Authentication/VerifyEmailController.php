<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\VerifyEmailRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AuthenticationMessageResource;
use App\Services\UserManagementService\Application\DTOs\Input\VerifyEmailDTO;
use App\Services\UserManagementService\Application\Services\Authentication\VerifyEmailService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/email/verification', methods: ['POST'], middleware: ['api', 'auth:sanctum', 'throttle:10,1'], name: 'auth.email.verify')]
#[Request(VerifyEmailRequest::class, VerifyEmailDTO::class)]
#[Service(VerifyEmailService::class, 'Email verified.', 200)]
#[Response(AuthenticationMessageResource::class)]
#[Doc(summary: 'Verify email', description: 'Verify the authenticated user email with a six-digit code.', tags: ['Authentication'])]
final class VerifyEmailController extends Controller {}
