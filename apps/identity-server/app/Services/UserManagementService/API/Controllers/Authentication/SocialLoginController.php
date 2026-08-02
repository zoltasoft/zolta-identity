<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\SocialLoginRequest;
use App\Services\UserManagementService\API\Resources\Authentication\SocialLoginResource;
use App\Services\UserManagementService\Application\DTOs\Input\SocialLoginDTO;
use App\Services\UserManagementService\Application\Services\Authentication\SocialLoginService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/{provider}/callback', methods: ['POST'], middleware: ['api'], name: 'auth.social.callback')]
#[Request(SocialLoginRequest::class, SocialLoginDTO::class)]
#[Service(SocialLoginService::class, 'Social login successful.')]
#[Response(SocialLoginResource::class)]
#[Doc(
    summary: 'Social login callback',
    description: 'Handle the OAuth provider callback and issue an application access token.',
    tags: ['Authentication']
)]
final class SocialLoginController extends Controller {}
