<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LegacyIdentityMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_importer_preserves_global_accounts_roles_permissions_and_assignments(): void
    {
        $sourcePath = sys_get_temp_dir().'/identity-legacy-'.Str::uuid().'.sqlite';
        touch($sourcePath);

        config()->set('database.connections.legacy_identity', [
            'driver' => 'sqlite',
            'database' => $sourcePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('legacy_identity');

        $schema = Schema::connection('legacy_identity');
        $schema->create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('role')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        $schema->create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->uuid('role_id')->nullable();
            $table->string('terms')->default('declined');
            $table->timestamps();
        });
        $schema->create('permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        $schema->create('permission_role', function (Blueprint $table): void {
            $table->uuid('permission_id');
            $table->uuid('role_id');
        });

        $roleId = (string) Str::uuid();
        $userId = (string) Str::uuid();
        $permissionId = (string) Str::uuid();
        $source = DB::connection('legacy_identity');
        $source->table('roles')->insert([
            'id' => $roleId,
            'role' => 'Administrator',
            'description' => 'Legacy administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $source->table('users')->insert([
            'id' => $userId,
            'username' => 'legacy-owner',
            'email' => 'LEGACY-OWNER@example.com',
            'password' => password_hash('correct-password', PASSWORD_BCRYPT),
            'role_id' => $roleId,
            'terms' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $source->table('permissions')->insert([
            'id' => $permissionId,
            'name' => 'users.manage',
            'description' => 'Manage users',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $source->table('permission_role')->insert([
            'permission_id' => $permissionId,
            'role_id' => $roleId,
        ]);

        try {
            $this->assertSame(0, Artisan::call('identity:migrate-legacy', [
                '--connection' => 'legacy_identity',
                '--no-rotate-clients' => true,
            ]));

            $this->assertDatabaseHas('roles', ['id' => $roleId, 'role' => 'Administrator']);
            $this->assertDatabaseHas('users', [
                'id' => $userId,
                'email' => 'legacy-owner@example.com',
                'role_id' => $roleId,
            ]);
            $this->assertDatabaseHas('permissions', [
                'id' => $permissionId,
                'name' => 'users.manage',
            ]);
            $this->assertDatabaseHas('permission_role', [
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        } finally {
            DB::disconnect('legacy_identity');
            @unlink($sourcePath);
        }
    }
}
