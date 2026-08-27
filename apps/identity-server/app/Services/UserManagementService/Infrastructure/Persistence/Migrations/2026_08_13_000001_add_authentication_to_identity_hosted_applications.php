<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('identity_hosted_applications', 'authentication')) {
            Schema::table('identity_hosted_applications', function (Blueprint $table): void {
                $table->json('authentication')->nullable()->after('appearance');
            });
        }

        if (! Schema::hasTable('identity_hosted_application_consents')) {
            Schema::create('identity_hosted_application_consents', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('hosted_application_id');
                $table->foreign('hosted_application_id', 'identity_hosted_consents_app_fk')
                    ->references('id')->on('identity_hosted_applications')->cascadeOnDelete();
                $table->uuid('user_id');
                $table->foreign('user_id', 'identity_hosted_consents_user_fk')
                    ->references('id')->on('users')->cascadeOnDelete();
                $table->string('terms_url', 2048)->nullable();
                $table->timestamp('accepted_at');
                $table->timestamps();
                $table->unique(['hosted_application_id', 'user_id'], 'identity_hosted_consents_app_user_uq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_hosted_application_consents');
        Schema::table('identity_hosted_applications', function (Blueprint $table): void {
            $table->dropColumn('authentication');
        });
    }
};
