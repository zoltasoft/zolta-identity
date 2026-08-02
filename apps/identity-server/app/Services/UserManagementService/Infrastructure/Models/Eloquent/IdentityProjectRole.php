<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class IdentityProjectRole extends Model
{
    use HasUuids;

    protected $table = 'identity_project_roles';

    protected $fillable = ['project_id', 'name', 'slug', 'description'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(IdentityProject::class, 'project_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(IdentityProjectPermission::class, 'identity_project_role_permission', 'role_id', 'permission_id');
    }

    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(IdentityProjectMembership::class, 'identity_membership_role', 'role_id', 'membership_id');
    }
}
