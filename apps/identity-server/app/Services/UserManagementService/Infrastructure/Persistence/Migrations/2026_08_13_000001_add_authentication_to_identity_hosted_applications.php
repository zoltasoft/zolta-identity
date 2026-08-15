<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_hosted_applications', function (Blueprint $table): void {
            $table->json('authentication')->nullable()->after('appearance');
        });

        Schema::create('identity_hosted_application_consents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('hosted_application_id')->constrained('identity_hosted_applications')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('terms_url', 2048)->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();
            $table->unique(['hosted_application_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_hosted_application_consents');
        Schema::table('identity_hosted_applications', function (Blueprint $table): void {
            $table->dropColumn('authentication');
        });
    }
};
