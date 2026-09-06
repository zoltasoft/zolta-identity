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
        'name', 'slug', 'description', 'status', 'mode', 'sandbox_ttl_minutes', 'registration_mode', 'registration_role_id', 'email_verification_required', 'google_social_authentication_enabled', 'deletion_scheduled_at', 'deletion_previous_status',
    ];

    protected $casts = [
        'sandbox_ttl_minutes' => 'integer',
        'email_verification_required' => 'boolean',
        'google_social_authentication_enabled' => 'boolean',
        'deletion_scheduled_at' => 'datetime',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(IdentityProjectClient::class, 'project_id');
    }

    public function hostedApplications(): HasMany
    {
        return $this->hasMany(IdentityHostedApplication::class, 'project_id');
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
