<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_system_admin')->default(false)->after('language_preference');
        });

        Schema::create('identity_projects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('identity_project_clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('secret_hash', 64);
            $table->string('secret_prefix', 16);
            $table->string('status')->default('active');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'name']);
        });

        Schema::create('identity_project_memberships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->boolean('is_admin')->default(false);
            $table->unsignedBigInteger('authorization_version')->default(1);
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('identity_project_roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'slug']);
        });

        Schema::create('identity_project_permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->foreignUuid('source_client_id')->nullable()->constrained('identity_project_clients')->nullOnDelete();
            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('source')->default('manual');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['project_id', 'key']);
        });

        Schema::create('identity_membership_role', function (Blueprint $table): void {
            $table->foreignUuid('membership_id')->constrained('identity_project_memberships')->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained('identity_project_roles')->cascadeOnDelete();
            $table->primary(['membership_id', 'role_id']);
        });

        Schema::create('identity_project_role_permission', function (Blueprint $table): void {
            $table->foreignUuid('role_id')->constrained('identity_project_roles')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('identity_project_permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('identity_membership_permission', function (Blueprint $table): void {
            $table->foreignUuid('membership_id')->constrained('identity_project_memberships')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('identity_project_permissions')->cascadeOnDelete();
            $table->primary(['membership_id', 'permission_id']);
        });

        Schema::create('identity_project_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->foreignUuid('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('token_hash', 64)->unique();
            $table->boolean('is_admin')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'email']);
        });

        Schema::create('identity_refresh_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('family_id')->index();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('identity_project_clients')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->uuid('rotated_to_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('identity_audit_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->nullable()->constrained('identity_projects')->nullOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained('identity_project_clients')->nullOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'event']);
        });

        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->uuid('identity_project_id')->nullable()->index()->after('tokenable_id');
            $table->uuid('identity_client_id')->nullable()->index()->after('identity_project_id');
            $table->uuid('identity_refresh_family_id')->nullable()->index()->after('identity_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->dropColumn(['identity_project_id', 'identity_client_id', 'identity_refresh_family_id']);
        });

        Schema::dropIfExists('identity_audit_events');
        Schema::dropIfExists('identity_refresh_tokens');
        Schema::dropIfExists('identity_project_invitations');
        Schema::dropIfExists('identity_membership_permission');
        Schema::dropIfExists('identity_project_role_permission');
        Schema::dropIfExists('identity_membership_role');
        Schema::dropIfExists('identity_project_permissions');
        Schema::dropIfExists('identity_project_roles');
        Schema::dropIfExists('identity_project_memberships');
        Schema::dropIfExists('identity_project_clients');
        Schema::dropIfExists('identity_projects');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_system_admin');
        });
    }
};
