<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

final class LegacyGlobalRbacRemovalMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_removal_preserves_users_and_project_catalog_authorization(): void
    {
        $migration = $this->migration();
        $migration->down();

        $defaultRoleId = (string) Str::uuid();
        DB::table('roles')->insert([
            'id' => $defaultRoleId,
            'role' => 'User',
            'description' => 'Default legacy role',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $userId,
            'username' => 'preserved-user',
            'email' => 'preserved@example.com',
            'password' => password_hash('correct-password', PASSWORD_BCRYPT),
            'terms' => 'accepted',
            'role_id' => $defaultRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catalogPermissionId = (string) Str::uuid();
        DB::table('identity_access_catalog_permissions')->insert([
            'id' => $catalogPermissionId,
            'key' => 'documents.read',
            'name' => 'Read documents',
            'description' => null,
            'status' => 'active',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration->up();

        $this->assertDatabaseHas('users', ['id' => $userId, 'email' => 'preserved@example.com']);
        $this->assertDatabaseHas('identity_access_catalog_permissions', ['id' => $catalogPermissionId]);
        $this->assertFalse(Schema::hasColumn('users', 'role_id'));
        $this->assertFalse(Schema::hasTable('roles'));
        $this->assertFalse(Schema::hasTable('permissions'));
        $this->assertFalse(Schema::hasTable('permission_role'));
        $this->assertFalse(Schema::hasTable('permission_user'));
    }

    public function test_removal_refuses_non_default_roles(): void
    {
        $migration = $this->migration();
        $migration->down();
        DB::table('roles')->insert([
            'id' => (string) Str::uuid(),
            'role' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('roles has 1 non-default role(s)');
        $migration->up();
    }

    public function test_removal_refuses_permissions_and_assignments(): void
    {
        $migration = $this->migration();
        $migration->down();
        $roleId = (string) Str::uuid();
        $permissionId = (string) Str::uuid();
        DB::table('roles')->insert([
            'id' => $roleId,
            'role' => 'User',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('permissions')->insert([
            'id' => $permissionId,
            'name' => 'users.manage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('permission_role')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);
        $userId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $userId,
            'username' => 'assigned-user',
            'email' => 'assigned@example.com',
            'password' => password_hash('correct-password', PASSWORD_BCRYPT),
            'terms' => 'accepted',
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('permission_user')->insert([
            'user_id' => $userId,
            'permission_id' => $permissionId,
        ]);

        try {
            $migration->up();
            $this->fail('The migration should refuse legacy authorization assignments.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('permission_role has 1 assignment(s)', $exception->getMessage());
            $this->assertStringContainsString('permission_user has 1 assignment(s)', $exception->getMessage());
            $this->assertStringContainsString('permissions has 1 record(s)', $exception->getMessage());
        }
    }

    private function migration(): Migration
    {
        return require app_path(
            'Services/UserManagementService/Infrastructure/Persistence/Migrations/2026_08_22_000001_remove_legacy_global_rbac.php'
        );
    }
}
