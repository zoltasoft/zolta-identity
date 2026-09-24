<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('identity_hosted_applications', 'auth_page_set')) {
            Schema::table('identity_hosted_applications', function (Blueprint $table): void {
                $table->string('auth_page_set', 100)->default('default')->after('callback_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('identity_hosted_applications', 'auth_page_set')) {
            Schema::table('identity_hosted_applications', function (Blueprint $table): void {
                $table->dropColumn('auth_page_set');
            });
        }
    }
};
