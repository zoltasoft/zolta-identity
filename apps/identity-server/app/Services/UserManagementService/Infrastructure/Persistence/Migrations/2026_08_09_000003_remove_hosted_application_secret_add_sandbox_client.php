<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_hosted_applications', function (Blueprint $table): void {
            $table->foreignUuid('sandbox_client_id')
                ->nullable()
                ->after('primary_client_id')
                ->constrained('identity_project_clients')
                ->nullOnDelete();
            $table->dropColumn('client_secret');
        });
    }

    public function down(): void
    {
        Schema::table('identity_hosted_applications', function (Blueprint $table): void {
            $table->text('client_secret')->nullable()->after('callback_url');
            $table->dropConstrainedForeignId('sandbox_client_id');
        });
    }
};
