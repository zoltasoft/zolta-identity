<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\UserManagementService\Domain\Aggregates\IdentityPermission;
use App\Services\UserManagementService\Domain\Enums\IdentityPermissionSource;
use App\Services\UserManagementService\Domain\Enums\IdentityPermissionStatus;
use App\Services\UserManagementService\Domain\Exceptions\InvalidIdentityAccessConfigurationException;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use PHPUnit\Framework\TestCase;

final class IdentityPermissionTest extends TestCase
{
    public function test_manifest_sync_owns_source_and_lifecycle_transitions(): void
    {
        $clientId = new IdentityClientId;
        $permission = IdentityPermission::createManual(
            new IdentityProjectId,
            'documents.read',
            'Read documents',
        );

        $permission->synchronizeFromManifest($clientId, 'Read project documents', null);
        $permission->markStale();

        $this->assertSame($clientId, $permission->sourceClientId());
        $this->assertSame(IdentityPermissionSource::Manifest, $permission->source());
        $this->assertSame(IdentityPermissionStatus::Stale, $permission->status());
    }

    public function test_permission_rejects_a_malformed_key(): void
    {
        $this->expectException(InvalidIdentityAccessConfigurationException::class);

        IdentityPermission::createManual(
            new IdentityProjectId,
            'Documents Read',
            'Read documents',
        );
    }
}
