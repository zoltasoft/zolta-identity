<?php

namespace App\Services\UserManagementService\API\Controllers\Users;

use App\Services\UserManagementService\API\Requests\Users\UpdateAccountProfileRequest;
use App\Services\UserManagementService\API\Resources\Users\AccountProfileResource;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateAccountProfileDTO;
use App\Services\UserManagementService\Application\Services\Users\UpdateAccountProfileService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'users/profile', methods: ['PUT'], middleware: ['api', 'auth:sanctum'], name: 'profile.update')]
#[Request(UpdateAccountProfileRequest::class, UpdateAccountProfileDTO::class)]
#[Service(UpdateAccountProfileService::class, 'Account profile updated.')]
#[Response(AccountProfileResource::class)]
#[Doc(
    summary: 'Update account profile',
    description: 'Update the name, email, and avatar of the authenticated user.',
    tags: ['Users']
)]
final class UpdateAccountProfileController extends Controller {}
