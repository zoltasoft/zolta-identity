<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class IdentityAuditEvent extends Model
{
    use HasUuids;

    protected $table = 'identity_audit_events';

    protected $fillable = [
        'project_id', 'client_id', 'actor_user_id', 'event', 'target_type', 'target_id',
        'metadata', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
