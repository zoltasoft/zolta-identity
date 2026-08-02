<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('email_verification_code_hash', 64)->nullable();
            $table->timestamp('email_verification_expires_at')->nullable();
        });

        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->string('registration_mode')->default('invite_only');
            $table->uuid('registration_role_id')->nullable();
            $table->foreign('registration_role_id')
                ->references('id')
                ->on('identity_project_roles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->dropForeign(['registration_role_id']);
            $table->dropColumn(['registration_mode', 'registration_role_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'email_verification_code_hash',
                'email_verification_expires_at',
            ]);
        });
    }
};
