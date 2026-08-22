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
        Schema::table('identity_project_permissions', function (Blueprint $table): void {
            $table->string('catalog_origin')->nullable()->after('catalog_version');
        });
        Schema::table('identity_project_roles', function (Blueprint $table): void {
            $table->string('catalog_origin')->nullable()->after('catalog_version');
        });

        DB::table('identity_project_permissions')->whereNotNull('catalog_permission_id')->update(['catalog_origin' => 'imported']);
        DB::table('identity_project_roles')->whereNotNull('catalog_role_id')->update(['catalog_origin' => 'imported']);

        $publishedPermissionIds = DB::table('identity_audit_events')
            ->where('event', 'access_catalog.permission_published')
            ->whereNotNull('target_id')
            ->pluck('target_id');
        $publishedRoleIds = DB::table('identity_audit_events')
            ->where('event', 'access_catalog.role_published')
            ->whereNotNull('target_id')
            ->pluck('target_id');

        DB::table('identity_project_permissions')->whereIn('id', $publishedPermissionIds)->update(['catalog_origin' => 'published']);
        DB::table('identity_project_roles')->whereIn('id', $publishedRoleIds)->update(['catalog_origin' => 'published']);
    }

    public function down(): void
    {
        Schema::table('identity_project_roles', fn (Blueprint $table) => $table->dropColumn('catalog_origin'));
        Schema::table('identity_project_permissions', fn (Blueprint $table) => $table->dropColumn('catalog_origin'));
    }
};
