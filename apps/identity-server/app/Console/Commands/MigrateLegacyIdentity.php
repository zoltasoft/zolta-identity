<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

final class MigrateLegacyIdentity extends Command
{
    protected $signature = 'identity:migrate-legacy
        {--connection=legacy_identity : Read-only source database connection}
        {--no-rotate-clients : Keep imported client hashes (not recommended)}';

    protected $description = 'Import identity-owned records from the legacy Portfolio database';

    /** @var array<string, list<string>> */
    private const TABLE_KEYS = [
        'roles' => ['id'],
        'social_providers' => ['id'],
        'permissions' => ['id'],
        'permission_role' => ['permission_id', 'role_id'],
        'permission_user' => ['permission_id', 'user_id'],
        'social_accounts' => ['id'],
        'identity_projects' => ['id'],
        'identity_project_clients' => ['id'],
        'identity_project_memberships' => ['id'],
        'identity_project_roles' => ['id'],
        'identity_project_permissions' => ['id'],
        'identity_membership_role' => ['membership_id', 'role_id'],
        'identity_project_role_permission' => ['role_id', 'permission_id'],
        'identity_membership_permission' => ['membership_id', 'permission_id'],
        'identity_project_invitations' => ['id'],
        'identity_audit_events' => ['id'],
    ];

    public function handle(): int
    {
        $sourceName = (string) $this->option('connection');
        $source = DB::connection($sourceName);
        $this->assertSourceIsSeparate($source);
        if (! Schema::connection($sourceName)->hasTable('users')) {
            $this->error("The {$sourceName} connection does not contain a users table.");

            return self::FAILURE;
        }

        $newClients = [];
        DB::transaction(function () use ($source, $sourceName, &$newClients): void {
            foreach (['roles', 'social_providers'] as $table) {
                if (Schema::connection($sourceName)->hasTable($table)) {
                    if ($table === 'roles' && DB::table('users')->doesntExist()) {
                        $sourceRoleIds = $source->table('roles')->pluck('id')->all();
                        DB::table('roles')->whereNotIn('id', $sourceRoleIds)->delete();
                    }
                    $this->copyTable($source, $sourceName, $table, self::TABLE_KEYS[$table]);
                }
            }

            $this->importUsers($source, $sourceName);
            foreach (self::TABLE_KEYS as $table => $keys) {
                if (in_array($table, ['roles', 'social_providers'], true)) {
                    continue;
                }
                if (! Schema::connection($sourceName)->hasTable($table)) {
                    continue;
                }
                if ($table === 'identity_project_clients') {
                    $newClients = $this->importClients($source, $sourceName);

                    continue;
                }
                $this->copyTable($source, $sourceName, $table, $keys);
            }

            DB::table('identity_refresh_tokens')->delete();
            DB::table('personal_access_tokens')->delete();
            $this->configureJobTrackerRegistration();
        });

        $rotated = [];
        if (! $this->option('no-rotate-clients')) {
            foreach ($newClients as $client) {
                $secret = Str::random(64);
                DB::table('identity_project_clients')->where('id', $client->id)->update([
                    'secret_hash' => hash('sha256', $secret),
                    'secret_prefix' => Str::substr($secret, 0, 8),
                    'updated_at' => now(),
                ]);
                $rotated[] = [$client->name, $client->id, $secret];
            }
        }

        $this->info('Legacy identity records imported. Existing access and refresh sessions were intentionally invalidated.');
        if ($rotated !== []) {
            $this->warn('Store these new client secrets now. They will not be displayed again.');
            $this->table(['Client', 'Client ID', 'New secret'], $rotated);
        }

        return self::SUCCESS;
    }

    private function assertSourceIsSeparate(ConnectionInterface $source): void
    {
        $destination = DB::connection();
        if ($source->getDriverName() === $destination->getDriverName()
            && $source->getDatabaseName() === $destination->getDatabaseName()) {
            throw new RuntimeException('The legacy source and identity destination databases must be different.');
        }
    }

    private function importUsers(ConnectionInterface $source, string $sourceName): void
    {
        $destinationColumns = Schema::getColumnListing('users');
        $sourceColumns = Schema::connection($sourceName)->getColumnListing('users');
        $columns = array_values(array_intersect($destinationColumns, $sourceColumns));

        $source->table('users')->orderBy('id')->chunk(250, function ($users) use ($columns): void {
            foreach ($users as $legacy) {
                $row = [];
                foreach ($columns as $column) {
                    $row[$column] = $legacy->{$column};
                }
                $row['email'] = Str::lower((string) $legacy->email);
                $row['remember_token'] = null;

                DB::table('users')->insertOrIgnore($row);
            }
        });
    }

    /** @return list<object> */
    private function importClients(ConnectionInterface $source, string $sourceName): array
    {
        $existingIds = DB::table('identity_project_clients')->pluck('id')->all();
        $this->copyTable($source, $sourceName, 'identity_project_clients', ['id'], ['secret_hash', 'secret_prefix']);

        return DB::table('identity_project_clients')
            ->whereNotIn('id', $existingIds)
            ->get(['id', 'name'])
            ->all();
    }

    /** @param list<string> $keys @param list<string> $preserveOnUpdate */
    private function copyTable(
        ConnectionInterface $source,
        string $sourceName,
        string $table,
        array $keys,
        array $preserveOnUpdate = [],
    ): void {
        $destinationColumns = Schema::getColumnListing($table);
        $sourceColumns = Schema::connection($sourceName)->getColumnListing($table);
        $columns = array_values(array_intersect($destinationColumns, $sourceColumns));
        $updates = array_values(array_diff($columns, $keys, $preserveOnUpdate));
        $orderColumn = $keys[0];

        $source->table($table)->orderBy($orderColumn)->chunk(250, function ($records) use ($table, $columns, $keys, $updates): void {
            $rows = collect($records)->map(function (object $record) use ($columns): array {
                $row = [];
                foreach ($columns as $column) {
                    $row[$column] = $record->{$column};
                }

                return $row;
            })->all();
            if ($rows !== []) {
                DB::table($table)->upsert($rows, $keys, $updates);
            }
        });
    }

    private function configureJobTrackerRegistration(): void
    {
        $project = DB::table('identity_projects')->where('slug', 'interviewlike-job-tracker')->first();
        if ($project === null) {
            return;
        }
        $memberRoleId = DB::table('identity_project_roles')
            ->where('project_id', $project->id)
            ->where('slug', 'member')
            ->value('id');
        DB::table('identity_projects')->where('id', $project->id)->update([
            'registration_mode' => 'public',
            'registration_role_id' => $memberRoleId,
        ]);
    }
}
