<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\UpdateSecurityPreferencesRequest;
use App\Services\UserManagementService\API\Resources\Users\SecurityPreferencesResource;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateSecurityPreferencesDTO;
use App\Services\UserManagementService\Application\Services\Users\UpdateSecurityPreferencesService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'users/profile/security', methods: ['PUT'], middleware: ['api', 'auth:sanctum'], name: 'profile.security.update')]
#[Request(UpdateSecurityPreferencesRequest::class, UpdateSecurityPreferencesDTO::class)]
#[Service(UpdateSecurityPreferencesService::class, 'Security preferences updated.')]
#[Response(SecurityPreferencesResource::class)]
#[Doc(
    summary: 'Update security preferences',
    description: 'Toggle 2FA, login alerts, and configure recovery email for the authenticated user.',
    tags: ['Users']
)]
final class UpdateSecurityPreferencesController extends Controller {}
