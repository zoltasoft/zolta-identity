<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\AcceptIdentityInvitation;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\IssueIdentityAccess;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ManageIdentitySessions;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ReadIdentityAccessContext;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ReadIdentitySessions;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\RecoverIdentityPassword;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\SyncIdentityClientManifest;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\VerifyIdentityEmail;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ConfigureIdentityProjectEnvironment;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ConfigureIdentityProjectRegistration;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\CreateIdentityProject;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityClients;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectAccess;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityWebhooks;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ReadIdentityProjects;
use App\Services\UserManagementService\Application\Contracts\IdentityInstallationServiceInterface;
use App\Services\UserManagementService\Domain\Repositories\IdentityClientRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityPermissionRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityProjectRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityRoleRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityWebhookRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityClientRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityMembershipRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityPermissionRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityRoleRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityWebhookRepository;
use App\Services\UserManagementService\Infrastructure\Services\EloquentIdentityAuthenticationService;
use App\Services\UserManagementService\Infrastructure\Services\EloquentIdentityInstallationService;
use App\Services\UserManagementService\Infrastructure\Services\EloquentIdentityProjectService;
use Illuminate\Routing\Route;
use Tests\TestCase;
use Zolta\Http\Router\Laravel\Bootstrap\AutoInvokeProxyController;

final class IdentityArchitectureTest extends TestCase
{
    public function test_identity_api_routes_are_owned_by_the_zolta_attribute_pipeline(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(static fn (Route $route): bool => str_starts_with($route->uri(), 'api/v1/identity/'));

        $this->assertCount(38, $routes);

        $routes->each(function (Route $route): void {
            $this->assertSame(
                AutoInvokeProxyController::class.'@__invoke',
                $route->getActionName(),
                "Identity route [{$route->uri()}] bypasses the Zolta attribute pipeline.",
            );
            $this->assertNotEmpty($route->getName());
        });
    }

    public function test_identity_contracts_resolve_to_focused_infrastructure_adapters(): void
    {
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(IssueIdentityAccess::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(ReadIdentityAccessContext::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(VerifyIdentityEmail::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(RecoverIdentityPassword::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(ManageIdentitySessions::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(ReadIdentitySessions::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(AcceptIdentityInvitation::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityAuthenticationService::class,
            app(SyncIdentityClientManifest::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityInstallationService::class,
            app(IdentityInstallationServiceInterface::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectService::class,
            app(CreateIdentityProject::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectService::class,
            app(ConfigureIdentityProjectRegistration::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectService::class,
            app(ConfigureIdentityProjectEnvironment::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectService::class,
            app(ManageIdentityWebhooks::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectService::class,
            app(ManageIdentityClients::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectService::class,
            app(ManageIdentityProjectAccess::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectService::class,
            app(ReadIdentityProjects::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityProjectRepository::class,
            app(IdentityProjectRepository::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityClientRepository::class,
            app(IdentityClientRepository::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityMembershipRepository::class,
            app(IdentityMembershipRepository::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityPermissionRepository::class,
            app(IdentityPermissionRepository::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityRoleRepository::class,
            app(IdentityRoleRepository::class),
        );
        $this->assertInstanceOf(
            EloquentIdentityWebhookRepository::class,
            app(IdentityWebhookRepository::class),
        );
    }
}
