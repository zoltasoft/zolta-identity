<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->string('mode')->default('live')->after('status');
            $table->unsignedSmallInteger('sandbox_ttl_minutes')->default(60)->after('mode');
        });
    }

    public function down(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->dropColumn(['mode', 'sandbox_ttl_minutes']);
        });
    }
};
