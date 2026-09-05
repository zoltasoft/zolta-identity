<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->boolean('google_social_authentication_enabled')->default(false)->after('email_verification_required');
        });

        // A project is enabled when any of its hosted applications had Google
        // enabled. Hosted applications keep their independent terms documents.
        DB::table('identity_projects')->orderBy('id')->each(function (object $project): void {
            $enabled = DB::table('identity_hosted_applications')
                ->where('project_id', $project->id)
                ->whereNotNull('authentication')
                ->get(['authentication'])
                ->contains(function (object $application): bool {
                    $authentication = json_decode((string) $application->authentication, true);

                    return (bool) ($authentication['google_enabled'] ?? false);
                });
            if ($enabled) {
                DB::table('identity_projects')->where('id', $project->id)->update(['google_social_authentication_enabled' => true]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('identity_projects', function (Blueprint $table): void {
            $table->dropColumn('google_social_authentication_enabled');
        });
    }
};
