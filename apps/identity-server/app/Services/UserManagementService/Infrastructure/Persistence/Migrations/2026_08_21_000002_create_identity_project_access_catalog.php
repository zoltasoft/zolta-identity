<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_access_catalog_permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key', 160)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
        Schema::create('identity_access_catalog_roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
        Schema::create('identity_access_catalog_role_permission', function (Blueprint $table): void {
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->primary(['role_id', 'permission_id']);
            $table->foreign('role_id')->references('id')->on('identity_access_catalog_roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('identity_access_catalog_permissions')->cascadeOnDelete();
        });
        Schema::table('identity_project_permissions', function (Blueprint $table): void {
            $table->uuid('catalog_permission_id')->nullable()->index()->after('source_client_id');
            $table->unsignedInteger('catalog_version')->nullable()->after('catalog_permission_id');
        });
        Schema::table('identity_project_roles', function (Blueprint $table): void {
            $table->uuid('catalog_role_id')->nullable()->index()->after('project_id');
            $table->unsignedInteger('catalog_version')->nullable()->after('catalog_role_id');
        });
    }

    public function down(): void
    {
        Schema::table('identity_project_roles', fn (Blueprint $table) => $table->dropColumn(['catalog_role_id', 'catalog_version']));
        Schema::table('identity_project_permissions', fn (Blueprint $table) => $table->dropColumn(['catalog_permission_id', 'catalog_version']));
        Schema::dropIfExists('identity_access_catalog_role_permission');
        Schema::dropIfExists('identity_access_catalog_roles');
        Schema::dropIfExists('identity_access_catalog_permissions');
    }
};
