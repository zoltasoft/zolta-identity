<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class IdentityWebhookEndpoint extends Model
{
    use HasUuids;

    protected $table = 'identity_webhook_endpoints';

    protected $fillable = ['project_id', 'url', 'events', 'secret', 'secret_prefix', 'status', 'last_delivered_at'];

    protected $hidden = ['secret'];

    protected $casts = [
        'events' => 'array',
        'secret' => 'encrypted',
        'last_delivered_at' => 'datetime',
    ];
}
