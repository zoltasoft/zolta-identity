<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'theme_preference')) {
                $table->string('theme_preference')->default('system')->after('login_alerts_enabled');
            }

            if (! Schema::hasColumn('users', 'language_preference')) {
                $table->string('language_preference')->default('en-US')->after('theme_preference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'language_preference')) {
                $table->dropColumn('language_preference');
            }
            if (Schema::hasColumn('users', 'theme_preference')) {
                $table->dropColumn('theme_preference');
            }
        });
    }
};
