<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership as DomainIdentityMembership;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Role;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityClientProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Zolta\Domain\ValueObjects\UserId;

final class BootstrapIdentity extends Command
{
    protected $signature = 'identity:bootstrap
        {email : Installation owner email}
        {--name= : Installation owner display name}
        {--password= : Owner password; omit to enter it securely}
        {--project=Identity Console : Initial management project name}
        {--client=Identity Console BFF : Initial confidential client name}';

    protected $description = 'Create the first identity installation owner, project, and confidential console client';

    public function handle(
        IdentityClientProvisioner $clients,
        IdentityMembershipRepository $memberships,
    ): int {
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

        [$user, $project, $client, $secret] = DB::transaction(function () use ($clients, $memberships, $email, $name, $password): array {
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
            $projectId = IdentityProjectId::fromString($project->id);
            $userId = new UserId((string) $user->id);
            $membership = $memberships->findForProjectUser($projectId, $userId)
                ?? DomainIdentityMembership::create($projectId, $userId, true);
            $membership->acceptInvitation(true);
            $memberships->save($membership);
            [$client, $secret] = $clients->create($project->id, (string) $this->option('client'));

            return [$user, $project, $client, $secret];
        });

        $this->info('Identity installation bootstrapped. Store this client secret now; it will not be shown again.');
        $this->table(['Owner', 'Project ID', 'Client ID', 'Client secret'], [[$user->email, $project->id, $client->id, $secret]]);

        return self::SUCCESS;
    }
}
