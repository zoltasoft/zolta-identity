<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IdentityProjectAccount extends Model
{
    use HasUuids;

    protected $table = 'identity_project_accounts';

    protected $fillable = [
        'project_id', 'user_id', 'username', 'password', 'profile_picture',
        'email_verified_at', 'email_verification_code_hash',
        'email_verification_expires_at', 'status', 'password_changed_at',
        'last_authenticated_at',
    ];

    protected $hidden = ['password', 'email_verification_code_hash'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_authenticated_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(IdentityProject::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
