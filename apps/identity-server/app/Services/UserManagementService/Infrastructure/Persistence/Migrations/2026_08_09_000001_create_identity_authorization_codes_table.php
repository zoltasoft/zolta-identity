<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_authorization_codes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('identity_project_clients')->cascadeOnDelete();
            $table->uuid('source_refresh_family_id')->nullable()->index();
            $table->string('code_hash', 64)->unique();
            $table->string('redirect_uri', 2048);
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_authorization_codes');
    }
};
