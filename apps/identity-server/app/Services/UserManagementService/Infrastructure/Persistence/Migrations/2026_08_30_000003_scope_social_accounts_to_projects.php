<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_accounts', function (Blueprint $table): void {
            $table->foreignUuid('project_id')->nullable()->after('user_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->dropUnique('social_accounts_provider_user_unique');
            $table->unique(['project_id', 'social_provider_id', 'social_provider_user_id'], 'social_accounts_project_provider_user_uq');
        });
    }

    public function down(): void
    {
        Schema::table('social_accounts', function (Blueprint $table): void {
            $table->dropUnique('social_accounts_project_provider_user_uq');
            $table->unique(['social_provider_id', 'social_provider_user_id'], 'social_accounts_provider_user_unique');
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
