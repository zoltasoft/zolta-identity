<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\UpdatePreferenceSettingsRequest;
use App\Services\UserManagementService\API\Resources\Users\PreferenceSettingsResource;
use App\Services\UserManagementService\Application\DTOs\Input\UpdatePreferenceSettingsDTO;
use App\Services\UserManagementService\Application\Services\Users\UpdatePreferenceSettingsService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'users/profile/preferences', methods: ['PUT'], middleware: ['api', 'auth:sanctum'], name: 'profile.preferences.update')]
#[Request(UpdatePreferenceSettingsRequest::class, UpdatePreferenceSettingsDTO::class)]
#[Service(UpdatePreferenceSettingsService::class, 'Preferences updated.')]
#[Response(PreferenceSettingsResource::class)]
#[Doc(
    summary: 'Update user preferences',
    description: 'Persist UI theme and language selection for the authenticated user.',
    tags: ['Users']
)]
final class UpdatePreferenceSettingsController extends Controller {}
