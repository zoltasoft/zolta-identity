<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class IdentityProjectPermission extends Model
{
    use HasUuids;

    protected $table = 'identity_project_permissions';

    protected $fillable = ['project_id', 'source_client_id', 'key', 'name', 'description', 'source', 'status'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(IdentityProject::class, 'project_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(IdentityProjectRole::class, 'identity_project_role_permission', 'permission_id', 'role_id');
    }
}
