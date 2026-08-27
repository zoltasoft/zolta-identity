<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class IdentityAccessCatalogRole extends Model
{
    use HasUuids;

    protected $table = 'identity_access_catalog_roles';

    protected $fillable = ['slug', 'name', 'description', 'status', 'version'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(IdentityAccessCatalogPermission::class, 'identity_access_catalog_role_permission', 'role_id', 'permission_id');
    }
}
