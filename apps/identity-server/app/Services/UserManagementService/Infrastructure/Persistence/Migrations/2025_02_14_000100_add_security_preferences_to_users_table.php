<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('profile_picture');
            }

            if (! Schema::hasColumn('users', 'login_alerts_enabled')) {
                $table->boolean('login_alerts_enabled')->default(true)->after('two_factor_enabled');
            }

            if (! Schema::hasColumn('users', 'backup_email')) {
                $table->string('backup_email')->nullable()->after('login_alerts_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'backup_email')) {
                $table->dropColumn('backup_email');
            }
            if (Schema::hasColumn('users', 'login_alerts_enabled')) {
                $table->dropColumn('login_alerts_enabled');
            }
            if (Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->dropColumn('two_factor_enabled');
            }
        });
    }
};
