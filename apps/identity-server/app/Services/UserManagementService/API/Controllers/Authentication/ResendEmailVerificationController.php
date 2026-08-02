<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\ResendEmailVerificationRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AuthenticationMessageResource;
use App\Services\UserManagementService\Application\DTOs\Input\ResendEmailVerificationDTO;
use App\Services\UserManagementService\Application\Services\Authentication\ResendEmailVerificationService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/email/verification/resend', methods: ['POST'], middleware: ['api', 'auth:sanctum', 'throttle:3,1'], name: 'auth.email.verification.resend')]
#[Request(ResendEmailVerificationRequest::class, ResendEmailVerificationDTO::class)]
#[Service(ResendEmailVerificationService::class, 'Verification code sent.', 200)]
#[Response(AuthenticationMessageResource::class)]
#[Doc(summary: 'Resend email verification', description: 'Send a fresh verification code to the authenticated user.', tags: ['Authentication'])]
final class ResendEmailVerificationController extends Controller {}
