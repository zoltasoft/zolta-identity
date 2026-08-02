<?php

namespace App\Services\UserManagementService\API\Controllers\Authentication;

use App\Services\UserManagementService\API\Requests\Authentication\ListAccountSessionsRequest;
use App\Services\UserManagementService\API\Resources\Authentication\AccountSessionCollectionResource;
use App\Services\UserManagementService\Application\DTOs\Input\ListAccountSessionsDTO;
use App\Services\UserManagementService\Application\Services\Authentication\ListAccountSessionsService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route(path: 'auth/sessions', methods: ['GET'], middleware: ['api', 'auth:sanctum'], name: 'auth.sessions.index')]
#[Request(ListAccountSessionsRequest::class, ListAccountSessionsDTO::class)]
#[Service(ListAccountSessionsService::class, 'Account sessions retrieved.', 200)]
#[Response(AccountSessionCollectionResource::class)]
#[Doc(summary: 'List active sessions', description: 'List authentication sessions owned by the authenticated user.', tags: ['Authentication'])]
final class ListAccountSessionsController extends Controller {}
