<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Role;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Services\EloquentIdentityAccessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class BootstrapIdentity extends Command
{
    protected $signature = 'identity:bootstrap
        {email : Installation owner email}
        {--name= : Installation owner display name}
        {--password= : Owner password; omit to enter it securely}
        {--project=Identity Console : Initial management project name}
        {--client=Identity Console BFF : Initial confidential client name}';

    protected $description = 'Create the first identity installation owner, project, and confidential console client';

    public function handle(EloquentIdentityAccessService $identity): int
    {
        if (User::query()->where('is_system_admin', true)->exists()) {
            $this->error('This identity installation has already been bootstrapped.');

            return self::FAILURE;
        }

        $email = Str::lower((string) $this->argument('email'));
        $name = (string) ($this->option('name') ?: Str::before($email, '@'));
        $password = (string) ($this->option('password') ?: $this->secret('Owner password'));
        if (mb_strlen($password) < 12) {
            $this->error('The owner password must be at least 12 characters.');

            return self::FAILURE;
        }

        [$user, $project, $client, $secret] = DB::transaction(function () use ($identity, $email, $name, $password): array {
            $defaultRole = Role::query()->firstOrCreate(
                ['role' => 'User'],
                ['description' => 'Default global identity role'],
            );
            $user = User::query()->firstOrNew(['email' => $email]);
            $user->fill([
                'id' => $user->id ?: (string) Str::uuid(),
                'username' => $name,
                'password' => $password,
                'role_id' => $user->role_id ?: $defaultRole->id,
                'terms' => 'accepted',
                'email_verified_at' => $user->email_verified_at ?: now(),
                'is_system_admin' => true,
            ])->save();

            $projectName = (string) $this->option('project');
            $project = IdentityProject::query()->firstOrCreate(
                ['slug' => Str::slug($projectName)],
                ['name' => $projectName, 'status' => 'active'],
            );
            IdentityProjectMembership::query()->updateOrCreate(
                ['project_id' => $project->id, 'user_id' => $user->id],
                ['status' => 'active', 'is_admin' => true],
            );
            [$client, $secret] = $identity->newClient($project->id, (string) $this->option('client'));

            return [$user, $project, $client, $secret];
        });

        $this->info('Identity installation bootstrapped. Store this client secret now; it will not be shown again.');
        $this->table(['Owner', 'Project ID', 'Client ID', 'Client secret'], [[$user->email, $project->id, $client->id, $secret]]);

        return self::SUCCESS;
    }
}
