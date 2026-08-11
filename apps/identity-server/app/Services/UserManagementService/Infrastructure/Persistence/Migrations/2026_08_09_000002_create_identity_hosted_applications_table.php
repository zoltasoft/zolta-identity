<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_hosted_applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->foreignUuid('primary_client_id')->constrained('identity_project_clients')->cascadeOnDelete();
            $table->string('key', 100)->unique();
            $table->string('name');
            $table->string('application_url', 2048);
            $table->string('callback_url', 2048);
            $table->text('client_secret');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['project_id', 'primary_client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_hosted_applications');
    }
};
