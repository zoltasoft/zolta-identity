<?php

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $project_id
 * @property string $social_provider_id
 * @property string $social_provider_user_id
 * @property string $access_token
 * @property string|null $access_token_expires_at
 * @property string|null $refresh_token
 * @property string|null $refresh_token_expires_at
 * @property string|null $avatar_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read SocialProvider|null $provider
 */
class SocialAccount extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'project_id',
        'social_provider_id',
        'social_provider_user_id',
        'access_token',
        'refresh_token',
        'avatar_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(SocialProvider::class);
    }
}
