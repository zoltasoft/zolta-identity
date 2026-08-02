<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership;
use App\Services\UserManagementService\Domain\Enums\IdentityMembershipStatus;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use PHPUnit\Framework\TestCase;
use Zolta\Domain\ValueObjects\UserId;

final class IdentityMembershipTest extends TestCase
{
    public function test_membership_access_change_invalidates_existing_authorization(): void
    {
        $membership = IdentityMembership::create(new IdentityProjectId, new UserId);
        $roleId = new IdentityRoleId;
        $permissionId = new IdentityPermissionId;

        $membership->updateAccess(
            [$roleId, $roleId],
            [$permissionId, $permissionId],
            true,
            IdentityMembershipStatus::Active,
        );

        $this->assertSame(2, $membership->authorizationVersion());
        $this->assertTrue($membership->isAdministrator());
        $this->assertCount(1, $membership->roleIds());
        $this->assertCount(1, $membership->permissionIds());
    }

    public function test_accepting_an_invitation_reactivates_and_invalidates_membership(): void
    {
        $membership = IdentityMembership::create(new IdentityProjectId, new UserId);
        $membership->updateAccess([], [], false, IdentityMembershipStatus::Suspended);

        $membership->acceptInvitation(true);

        $this->assertSame(3, $membership->authorizationVersion());
        $this->assertSame(IdentityMembershipStatus::Active, $membership->status());
        $this->assertTrue($membership->isAdministrator());
    }
}
