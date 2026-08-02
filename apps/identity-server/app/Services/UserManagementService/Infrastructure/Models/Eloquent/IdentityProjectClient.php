<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IdentityProjectClient extends Model
{
    use HasUuids;

    protected $table = 'identity_project_clients';

    protected $fillable = ['project_id', 'name', 'secret_hash', 'secret_prefix', 'status', 'last_used_at'];

    protected $hidden = ['secret_hash'];

    protected function casts(): array
    {
        return ['last_used_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(IdentityProject::class, 'project_id');
    }
}
