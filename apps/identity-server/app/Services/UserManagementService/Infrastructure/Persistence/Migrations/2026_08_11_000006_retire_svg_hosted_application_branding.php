<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $paths = DB::table('identity_hosted_applications')
            ->whereRaw('lower(logo_path) like ?', ['%.svg'])
            ->pluck('logo_path')
            ->filter()
            ->values()
            ->all();

        if ($paths === []) {
            return;
        }

        DB::table('identity_hosted_applications')
            ->whereIn('logo_path', $paths)
            ->update(['logo_path' => null, 'updated_at' => now()]);

        $disk = (string) config('zolta.identity.hosted_applications.branding_disk', 'public');
        Storage::disk($disk)->delete($paths);
    }

    public function down(): void
    {
        // SVG branding is intentionally removed and cannot be restored safely.
    }
};
