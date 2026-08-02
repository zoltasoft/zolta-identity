<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class IdentityWebhookDelivery extends Model
{
    use HasUuids;

    protected $table = 'identity_webhook_deliveries';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];
}
