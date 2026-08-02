<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('social_provider_id');
            $table->string('social_provider_user_id');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();

            // FIX: Use a shorter unique constraint name
            $table->unique(
                ['social_provider_id', 'social_provider_user_id'],
                'social_accounts_provider_user_unique' // ← Shorter name
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
