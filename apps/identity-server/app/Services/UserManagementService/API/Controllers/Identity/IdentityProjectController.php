<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\API\Requests\Identity\IdentityProjectOperationRequest;
use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use App\Services\UserManagementService\Application\Services\Identity\ExecuteIdentityProjectService;
use App\Services\UserManagementService\Application\Services\Identity\ReadIdentityProjectService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Service;

final class IdentityProjectController extends Controller
{
    private const MIDDLEWARE = ['api', 'auth:sanctum', 'identity.token'];

    #[Route('v1/identity/projects', methods: ['GET'], middleware: self::MIDDLEWARE, name: 'identity.projects.index')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ReadIdentityProjectService::class, 'Projects retrieved.')]
    public function index(): void {}

    #[Route('v1/identity/projects', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.store')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Project created.', 201)]
    public function store(): void {}

    #[Route('v1/identity/projects/{project}', methods: ['GET'], middleware: self::MIDDLEWARE, name: 'identity.projects.show')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ReadIdentityProjectService::class, 'Project retrieved.')]
    public function show(): void {}

    #[Route('v1/identity/projects/{project}/registration', methods: ['PATCH'], middleware: self::MIDDLEWARE, name: 'identity.projects.registration.update')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Registration policy updated.')]
    public function updateRegistration(): void {}

    #[Route('v1/identity/projects/{project}/environment', methods: ['PATCH'], middleware: self::MIDDLEWARE, name: 'identity.projects.environment.update')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Project environment updated.')]
    public function updateEnvironment(): void {}

    #[Route('v1/identity/projects/{project}/webhooks', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.webhooks.store')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Webhook created.', 201)]
    public function storeWebhook(): void {}

    #[Route('v1/identity/projects/{project}/webhooks/{webhook}', methods: ['PUT'], middleware: self::MIDDLEWARE, name: 'identity.projects.webhooks.update')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Webhook updated.')]
    public function updateWebhook(): void {}

    #[Route('v1/identity/projects/{project}/webhooks/{webhook}/rotate-secret', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.webhooks.rotate')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Webhook secret rotated.')]
    public function rotateWebhookSecret(): void {}

    #[Route('v1/identity/projects/{project}/webhooks/{webhook}', methods: ['DELETE'], middleware: self::MIDDLEWARE, name: 'identity.projects.webhooks.destroy')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Webhook removed.')]
    public function destroyWebhook(): void {}

    #[Route('v1/identity/projects/{project}/clients', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.clients.store')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Client created.', 201)]
    public function storeClient(): void {}

    #[Route('v1/identity/projects/{project}/clients/{client}/rotate-secret', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.clients.rotate')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Client secret rotated.')]
    public function rotateClient(): void {}

    #[Route('v1/identity/projects/{project}/clients/{client}', methods: ['PATCH'], middleware: self::MIDDLEWARE, name: 'identity.projects.clients.status')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Client status updated.')]
    public function setClientStatus(): void {}

    #[Route('v1/identity/projects/{project}/clients/{client}/permission-manifest', methods: ['PUT'], middleware: self::MIDDLEWARE, name: 'identity.projects.clients.manifest')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Permission manifest synchronized.')]
    public function syncManifest(): void {}

    #[Route('v1/identity/projects/{project}/hosted-applications', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.hosted_applications.store')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Hosted application created.', 201)]
    public function storeHostedApplication(): void {}

    #[Route('v1/identity/projects/{project}/hosted-applications/{hosted_application}', methods: ['PATCH'], middleware: self::MIDDLEWARE, name: 'identity.projects.hosted_applications.update')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Hosted application updated.')]
    public function updateHostedApplication(): void {}

    #[Route('v1/identity/projects/{project}/hosted-applications/{hosted_application}', methods: ['DELETE'], middleware: self::MIDDLEWARE, name: 'identity.projects.hosted_applications.destroy')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Hosted application removed.')]
    public function destroyHostedApplication(): void {}

    #[Route('v1/identity/projects/{project}/roles', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.roles.store')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Role created.', 201)]
    public function storeRole(): void {}

    #[Route('v1/identity/projects/{project}/permissions', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.permissions.store')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Permission created.', 201)]
    public function storePermission(): void {}

    #[Route('v1/identity/projects/{project}/roles/{role}/permissions', methods: ['PUT'], middleware: self::MIDDLEWARE, name: 'identity.projects.roles.permissions')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Role permissions updated.')]
    public function setRolePermissions(): void {}

    #[Route('v1/identity/projects/{project}/invitations', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.invitations.store')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Invitation created.', 201)]
    public function invite(): void {}

    #[Route('v1/identity/projects/{project}/memberships/{membership}/access', methods: ['PUT'], middleware: self::MIDDLEWARE, name: 'identity.projects.memberships.access')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Membership access updated.')]
    public function setMembershipAccess(): void {}

    #[Route('v1/identity/projects/{project}/memberships/{membership}', methods: ['DELETE'], middleware: self::MIDDLEWARE, name: 'identity.projects.memberships.destroy')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ExecuteIdentityProjectService::class, 'Membership removed.')]
    public function destroyMembership(): void {}

    #[Route('v1/identity/projects/{project}/audit', methods: ['GET'], middleware: self::MIDDLEWARE, name: 'identity.projects.audit')]
    #[Request(IdentityProjectOperationRequest::class, IdentityOperationDTO::class)]
    #[Service(ReadIdentityProjectService::class, 'Audit events retrieved.')]
    public function audit(): void {}
}
