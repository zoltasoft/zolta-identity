<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $conflicts = $this->legacyAuthorizationConflicts();
        if ($conflicts !== []) {
            throw new RuntimeException(
                'Legacy global RBAC removal was refused because authorization data still exists: '
                .implode('; ', $conflicts)
                .'. Migrate or archive this data before retrying.'
            );
        }

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permission_user');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }

        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    /**
     * Rollback restores only an empty compatibility schema. Deleted legacy RBAC
     * data cannot be reconstructed and must be restored from a database backup.
     */
    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('role')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->uuid('role_id')->nullable();
                $table->foreign('role_id')->references('id')->on('roles');
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table): void {
                $table->uuid('role_id');
                $table->uuid('permission_id');
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
                $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }

        if (! Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table): void {
                $table->uuid('user_id');
                $table->uuid('permission_id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
                $table->primary(['user_id', 'permission_id']);
            });
        }
    }

    /** @return list<string> */
    private function legacyAuthorizationConflicts(): array
    {
        $conflicts = [];

        foreach (['permission_role', 'permission_user'] as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    $conflicts[] = "{$table} has {$count} assignment(s)";
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $count = DB::table('permissions')->count();
            if ($count > 0) {
                $conflicts[] = "permissions has {$count} record(s)";
            }
        }

        if (Schema::hasTable('roles')) {
            $count = DB::table('roles')->where('role', '!=', 'User')->count();
            if ($count > 0) {
                $conflicts[] = "roles has {$count} non-default role(s)";
            }
        }

        return $conflicts;
    }
};
