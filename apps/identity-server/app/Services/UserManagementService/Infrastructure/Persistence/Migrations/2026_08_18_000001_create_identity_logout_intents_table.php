<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_logout_intents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('hosted_application_id')->constrained('identity_hosted_applications')->cascadeOnDelete();
            $table->string('intent_hash', 64)->unique();
            $table->string('return_url', 2048);
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_logout_intents');
    }
};
