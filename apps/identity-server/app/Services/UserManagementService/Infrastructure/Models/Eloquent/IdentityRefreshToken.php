<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class IdentityRefreshToken extends Model
{
    use HasUuids;

    protected $table = 'identity_refresh_tokens';

    protected $fillable = [
        'family_id', 'user_id', 'project_id', 'client_id', 'token_hash', 'rotated_to_id',
        'expires_at', 'used_at', 'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime', 'revoked_at' => 'datetime'];
    }
}
