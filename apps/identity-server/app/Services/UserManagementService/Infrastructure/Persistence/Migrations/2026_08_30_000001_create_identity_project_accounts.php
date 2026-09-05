<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('identity_project_accounts')) {
            Schema::create('identity_project_accounts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('username', 100);
                $table->string('password');
                $table->string('profile_picture')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('email_verification_code_hash', 64)->nullable();
                $table->timestamp('email_verification_expires_at')->nullable();
                $table->string('status')->default('active');
                $table->timestamp('password_changed_at')->nullable();
                $table->timestamp('last_authenticated_at')->nullable();
                $table->timestamps();
                $table->unique(['project_id', 'user_id']);
                $table->index(['project_id', 'status']);
            });
        }

        // Existing memberships represented usable project access. Copy their credential
        // state before the new login path is enabled so no member is locked out.
        DB::table('identity_project_memberships')
            ->join('users', 'users.id', '=', 'identity_project_memberships.user_id')
            ->orderBy('identity_project_memberships.id')
            ->select([
                'identity_project_memberships.project_id',
                'identity_project_memberships.user_id',
                'identity_project_memberships.status',
                'users.username',
                'users.password',
                'users.profile_picture',
                'users.email_verified_at',
                'users.email_verification_code_hash',
                'users.email_verification_expires_at',
                'users.created_at',
                'users.updated_at',
            ])
            ->each(function (object $membership): void {
                if (DB::table('identity_project_accounts')
                    ->where('project_id', $membership->project_id)
                    ->where('user_id', $membership->user_id)
                    ->exists()) {
                    return;
                }

                DB::table('identity_project_accounts')->insert([
                    'id' => (string) Str::uuid(),
                    'project_id' => $membership->project_id,
                    'user_id' => $membership->user_id,
                    'username' => $membership->username,
                    'password' => $membership->password,
                    'profile_picture' => $membership->profile_picture,
                    'email_verified_at' => $membership->email_verified_at,
                    'email_verification_code_hash' => $membership->email_verification_code_hash,
                    'email_verification_expires_at' => $membership->email_verification_expires_at,
                    'status' => $membership->status,
                    'password_changed_at' => $membership->updated_at,
                    'created_at' => $membership->created_at,
                    'updated_at' => $membership->updated_at,
                ]);
            });

        if (! Schema::hasTable('identity_project_password_reset_tokens')) {
            Schema::create('identity_project_password_reset_tokens', function (Blueprint $table): void {
                $table->uuid('project_account_id')->primary();
                $table->foreign('project_account_id', 'identity_project_reset_account_fk')
                    ->references('id')->on('identity_project_accounts')->cascadeOnDelete();
                $table->string('token_hash', 64);
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_project_password_reset_tokens');
        Schema::dropIfExists('identity_project_accounts');
    }
};
