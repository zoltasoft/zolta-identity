<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\UserManagementService\Domain\Aggregates\IdentityProject;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectRegistrationMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectStatus;
use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityProjectConfigurationException;
use PHPUnit\Framework\TestCase;

final class IdentityProjectTest extends TestCase
{
    public function test_new_project_starts_with_safe_production_defaults(): void
    {
        $project = IdentityProject::create('Job Tracker', 'job-tracker', 'Identity boundary');

        $this->assertSame('Job Tracker', $project->name());
        $this->assertSame('job-tracker', $project->slug());
        $this->assertSame(IdentityProjectStatus::Active, $project->status());
        $this->assertSame(IdentityProjectMode::Live, $project->mode());
        $this->assertSame(60, $project->sandboxTtlMinutes());
        $this->assertSame(
            IdentityProjectRegistrationMode::InviteOnly,
            $project->registrationMode(),
        );
        $this->assertNull($project->registrationRoleId());
        $this->assertTrue($project->emailVerificationRequired());
    }

    public function test_registration_and_environment_are_changed_through_domain_behavior(): void
    {
        $project = IdentityProject::create('Job Tracker', 'job-tracker');

        $project->configureRegistration(IdentityProjectRegistrationMode::Public, 'role-id', false);
        $project->configureEnvironment(IdentityProjectMode::Sandbox, 90);

        $this->assertSame(IdentityProjectRegistrationMode::Public, $project->registrationMode());
        $this->assertSame('role-id', $project->registrationRoleId());
        $this->assertFalse($project->emailVerificationRequired());
        $this->assertSame(IdentityProjectMode::Sandbox, $project->mode());
        $this->assertSame(90, $project->sandboxTtlMinutes());
    }

    public function test_sandbox_lifetime_is_enforced_inside_the_domain(): void
    {
        $project = IdentityProject::create('Job Tracker', 'job-tracker');

        $this->expectException(InvalidIdentityProjectConfigurationException::class);
        $project->configureEnvironment(IdentityProjectMode::Sandbox, 4);
    }
}
