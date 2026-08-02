<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class IdentityProject extends Model
{
    use HasUuids;

    protected $table = 'identity_projects';

    protected $fillable = [
        'name', 'slug', 'description', 'status', 'mode', 'sandbox_ttl_minutes', 'registration_mode', 'registration_role_id',
    ];

    protected $casts = [
        'sandbox_ttl_minutes' => 'integer',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(IdentityProjectClient::class, 'project_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(IdentityProjectMembership::class, 'project_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(IdentityProjectRole::class, 'project_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(IdentityProjectPermission::class, 'project_id');
    }
}
