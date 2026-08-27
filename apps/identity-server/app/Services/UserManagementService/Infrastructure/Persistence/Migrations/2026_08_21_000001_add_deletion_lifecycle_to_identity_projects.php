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
            $table->timestamp('deletion_scheduled_at')->nullable()->index()->after('status');
            $table->string('deletion_previous_status')->nullable()->after('deletion_scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->dropIndex(['deletion_scheduled_at']);
            $table->dropColumn(['deletion_scheduled_at', 'deletion_previous_status']);
        });
    }
};
