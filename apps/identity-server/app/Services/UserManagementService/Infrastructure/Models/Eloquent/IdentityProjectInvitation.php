<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class IdentityProjectInvitation extends Model
{
    use HasUuids;

    protected $table = 'identity_project_invitations';

    protected $fillable = ['project_id', 'invited_by', 'email', 'token_hash', 'is_admin', 'expires_at', 'accepted_at'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['is_admin' => 'boolean', 'expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }
}
