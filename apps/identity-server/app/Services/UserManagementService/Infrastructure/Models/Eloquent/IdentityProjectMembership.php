<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class IdentityProjectMembership extends Model
{
    use HasUuids;

    protected $table = 'identity_project_memberships';

    protected $fillable = ['project_id', 'user_id', 'status', 'is_admin', 'authorization_version'];

    protected function casts(): array
    {
        return ['is_admin' => 'boolean', 'authorization_version' => 'integer'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(IdentityProject::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(IdentityProjectRole::class, 'identity_membership_role', 'membership_id', 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(IdentityProjectPermission::class, 'identity_membership_permission', 'membership_id', 'permission_id');
    }

    /** @return list<string> */
    public function effectivePermissionKeys(): array
    {
        $direct = $this->permissions()->where('status', 'active')->pluck('key');
        $fromRoles = IdentityProjectPermission::query()
            ->where('identity_project_permissions.project_id', $this->project_id)
            ->where('identity_project_permissions.status', 'active')
            ->whereHas('roles.memberships', fn ($query) => $query->whereKey($this->getKey()))
            ->pluck('key');

        return $direct->merge($fromRoles)->unique()->sort()->values()->all();
    }

    /** @return list<string> */
    public function effectiveRoleSlugs(): array
    {
        return $this->roles()->pluck('slug')->unique()->sort()->values()->all();
    }
}
