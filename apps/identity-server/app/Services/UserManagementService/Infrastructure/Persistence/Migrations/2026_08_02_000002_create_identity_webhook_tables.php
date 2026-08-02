<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_webhook_endpoints', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('identity_projects')->cascadeOnDelete();
            $table->text('url');
            $table->json('events');
            $table->text('secret');
            $table->string('secret_prefix', 16);
            $table->string('status')->default('active');
            $table->timestamp('last_delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('identity_webhook_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('event_id')->index();
            $table->uuid('subject_id')->nullable()->index();
            $table->foreignUuid('endpoint_id')->constrained('identity_webhook_endpoints')->cascadeOnDelete();
            $table->string('event');
            $table->json('payload');
            $table->string('status')->default('queued')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('failure')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['endpoint_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_webhook_deliveries');
        Schema::dropIfExists('identity_webhook_endpoints');
    }
};
