<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->boolean('email_verification_required')->default(true)->after('registration_role_id');
        });
    }

    public function down(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->dropColumn('email_verification_required');
        });
    }
};
