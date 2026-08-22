<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class IdentityAccessCatalogPermission extends Model
{
    use HasUuids;

    protected $table = 'identity_access_catalog_permissions';

    protected $fillable = ['key', 'name', 'description', 'status', 'version'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(IdentityAccessCatalogRole::class, 'identity_access_catalog_role_permission', 'permission_id', 'role_id');
    }
}
