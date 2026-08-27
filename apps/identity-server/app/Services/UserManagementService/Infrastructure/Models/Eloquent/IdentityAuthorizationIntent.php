<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class IdentityAuthorizationIntent extends Model
{
    use HasUuids;

    protected $table = 'identity_authorization_intents';

    protected $fillable = [
        'hosted_application_id',
        'intent_hash',
        'state',
        'demo_account_enabled',
        'expires_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'demo_account_enabled' => 'boolean',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}
