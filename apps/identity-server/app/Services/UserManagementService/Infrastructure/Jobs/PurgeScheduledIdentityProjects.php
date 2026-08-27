<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Jobs;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityHostedApplication;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityAuditRecorder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class PurgeScheduledIdentityProjects implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function handle(IdentityAuditRecorder $audit): void
    {
        IdentityProject::query()
            ->where('status', 'pending_deletion')
            ->whereNotNull('deletion_scheduled_at')
            ->where('deletion_scheduled_at', '<=', now())
            ->orderBy('deletion_scheduled_at')
            ->pluck('id')
            ->each(fn (string $projectId) => $this->purge($projectId, $audit));
    }

    private function purge(string $projectId, IdentityAuditRecorder $audit): void
    {
        DB::transaction(function () use ($projectId, $audit): void {
            $project = IdentityProject::query()->lockForUpdate()->find($projectId);
            if ($project === null
                || $project->status !== 'pending_deletion'
                || $project->deletion_scheduled_at === null
                || $project->deletion_scheduled_at->isFuture()) {
                return;
            }

            $paths = IdentityHostedApplication::query()
                ->where('project_id', $projectId)
                ->whereNotNull('logo_path')
                ->pluck('logo_path')
                ->all();
            if ($paths !== [] && ! Storage::disk((string) config('zolta.identity.hosted_applications.branding_disk', 'public'))->delete($paths)) {
                throw new RuntimeException('Unable to remove hosted application branding during project purge.');
            }

            DB::table('personal_access_tokens')->where('identity_project_id', $projectId)->delete();
            DB::table('identity_refresh_tokens')->where('project_id', $projectId)->delete();
            DB::table('identity_authorization_codes')->where('project_id', $projectId)->delete();
            $audit->record('project.deleted', $projectId, null, null, 'project', $projectId, [
                'slug' => $project->slug,
                'scheduled_at' => $project->deletion_scheduled_at->toIso8601String(),
            ]);
            $project->delete();
        });
    }
}
