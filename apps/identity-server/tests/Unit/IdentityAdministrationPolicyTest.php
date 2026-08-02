<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\UserManagementService\Domain\Policies\IdentityAdministrationPolicy;
use PHPUnit\Framework\TestCase;

final class IdentityAdministrationPolicyTest extends TestCase
{
    private IdentityAdministrationPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new IdentityAdministrationPolicy;
    }

    public function test_administrator_cannot_lock_or_demote_their_own_installation_account(): void
    {
        $this->assertFalse(
            $this->policy->canUpdateInstallationAccount('actor', 'actor', false, false),
        );
        $this->assertFalse(
            $this->policy->canUpdateInstallationAccount('actor', 'actor', true, true),
        );
        $this->assertTrue(
            $this->policy->canUpdateInstallationAccount('actor', 'actor', true, false),
        );
        $this->assertTrue(
            $this->policy->canUpdateInstallationAccount('actor', 'other', false, true),
        );
    }

    public function test_project_administrator_cannot_suspend_or_demote_their_own_membership(): void
    {
        $this->assertFalse(
            $this->policy->canUpdateMembership('actor', 'actor', false, false, 'active'),
        );
        $this->assertFalse(
            $this->policy->canUpdateMembership('actor', 'actor', false, true, 'suspended'),
        );
        $this->assertTrue(
            $this->policy->canUpdateMembership('actor', 'actor', false, true, 'active'),
        );
        $this->assertTrue(
            $this->policy->canUpdateMembership('actor', 'actor', true, false, 'suspended'),
        );
    }

    public function test_project_administrator_cannot_remove_their_own_admin_membership(): void
    {
        $this->assertFalse($this->policy->canRemoveMembership('actor', 'actor', true));
        $this->assertTrue($this->policy->canRemoveMembership('actor', 'actor', false));
        $this->assertTrue($this->policy->canRemoveMembership('actor', 'other', true));
    }
}
