<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || Schema::hasColumn('roles', 'description')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasColumn('roles', 'description')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('description');
        });
    }
};
