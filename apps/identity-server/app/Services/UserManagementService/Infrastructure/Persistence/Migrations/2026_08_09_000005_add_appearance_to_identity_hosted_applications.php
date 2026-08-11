<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_hosted_applications', function (Blueprint $table): void {
            $table->json('appearance')->nullable()->after('callback_url');
            $table->string('logo_path', 2048)->nullable()->after('appearance');
        });
    }

    public function down(): void
    {
        Schema::table('identity_hosted_applications', function (Blueprint $table): void {
            $table->dropColumn(['appearance', 'logo_path']);
        });
    }
};
