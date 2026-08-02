<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\UserManagementService\Domain\Aggregates\IdentityRole;
use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityAccessConfigurationException;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use PHPUnit\Framework\TestCase;

final class IdentityRoleTest extends TestCase
{
    public function test_role_owns_its_unique_permission_assignment(): void
    {
        $role = IdentityRole::create(
            new IdentityProjectId,
            'Document editor',
            'document-editor',
        );
        $permissionId = new IdentityPermissionId;

        $role->assignPermissions([$permissionId, $permissionId]);

        $this->assertCount(1, $role->permissionIds());
        $this->assertSame($permissionId, $role->permissionIds()[0]);
    }

    public function test_role_requires_a_name_and_slug(): void
    {
        $this->expectException(InvalidIdentityAccessConfigurationException::class);

        IdentityRole::create(new IdentityProjectId, '', '');
    }
}
