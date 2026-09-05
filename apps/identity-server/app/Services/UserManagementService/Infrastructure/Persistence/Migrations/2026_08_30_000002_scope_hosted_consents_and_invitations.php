<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_project_invitations', function (Blueprint $table): void {
            $table->foreignUuid('hosted_application_id')->nullable()->after('project_id')->constrained('identity_hosted_applications')->nullOnDelete();
        });

        Schema::table('identity_hosted_application_consents', function (Blueprint $table): void {
            $table->foreignUuid('project_account_id')->nullable()->after('user_id')->constrained('identity_project_accounts')->cascadeOnDelete();
        });

        DB::table('identity_hosted_application_consents')
            ->join('identity_hosted_applications', 'identity_hosted_applications.id', '=', 'identity_hosted_application_consents.hosted_application_id')
            ->join('identity_project_accounts', function ($join): void {
                $join->on('identity_project_accounts.user_id', '=', 'identity_hosted_application_consents.user_id')
                    ->on('identity_project_accounts.project_id', '=', 'identity_hosted_applications.project_id');
            })
            ->select([
                'identity_hosted_application_consents.id as consent_id',
                'identity_project_accounts.id as project_account_id',
            ])
            ->get()
            ->each(function (object $consent): void {
                DB::table('identity_hosted_application_consents')
                    ->where('id', $consent->consent_id)
                    ->update(['project_account_id' => $consent->project_account_id]);
            });

        Schema::table('identity_hosted_application_consents', function (Blueprint $table): void {
            $table->unique(['hosted_application_id', 'project_account_id'], 'identity_hosted_consents_app_account_uq');
        });
    }

    public function down(): void
    {
        Schema::table('identity_hosted_application_consents', function (Blueprint $table): void {
            $table->dropUnique('identity_hosted_consents_app_account_uq');
            $table->dropConstrainedForeignId('project_account_id');
        });
        Schema::table('identity_project_invitations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('hosted_application_id');
        });
    }
};
