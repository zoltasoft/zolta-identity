<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class IdentityLogoutIntent extends Model
{
    use HasUuids;

    protected $table = 'identity_logout_intents';

    protected $fillable = ['hosted_application_id', 'intent_hash', 'return_url', 'expires_at', 'consumed_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consumed_at' => 'datetime'];
    }
}
