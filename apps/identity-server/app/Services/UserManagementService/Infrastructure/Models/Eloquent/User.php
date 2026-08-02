<?php

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use App\Services\UserManagementService\Infrastructure\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string|null $profile_picture
 * @property bool $two_factor_enabled
 * @property bool $login_alerts_enabled
 * @property string|null $backup_email
 * @property string|null $theme_preference
 * @property string|null $language_preference
 * @property string|null $role_id
 * @property float|null $credit
 * @property string|null $terms
 * @property string|null $provider_id
 * @property string|null $verification_code
 * @property string|null $verification_expires_at
 * @property string|null $email_verified_at
 * @property string|null $password_reset_token
 * @property string|null $password_reset_expires_at
 * @property bool $locked
 * @property bool $is_system_admin
 * @property string|null $lock_expiry
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $avatar_url
 * @property bool $is_temporary
 * @property Carbon|null $demo_expires_at
 * @property-read Role|null $role
 * @property-read Collection<int, Permission> $permissions
 * @property-read Collection<int, SocialAccount> $socialAccounts
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'username',
        'email',
        'password',
        'profile_picture',
        'two_factor_enabled',
        'login_alerts_enabled',
        'backup_email',
        'theme_preference',
        'language_preference',
        'role_id',
        'credit',
        'terms',
        'provider_id',
        'verification_code',
        'verification_expires_at',
        'email_verification_code_hash',
        'email_verification_expires_at',
        'email_verified_at',
        'is_temporary',
        'demo_expires_at',
        'is_system_admin',
        'locked',
        'lock_expiry',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code_hash',
    ];

    protected $appends = ['avatar_url'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function getAvatarUrlAttribute()
    {
        // Get the first social account's avatar (adjust logic if needed)
        if (! $this->relationLoaded('socialAccounts')) {
            return null;
        }

        return optional($this->socialAccounts->first())->avatar_url;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'login_alerts_enabled' => 'boolean',
        'theme_preference' => 'string',
        'language_preference' => 'string',
        'is_temporary' => 'boolean',
        'demo_expires_at' => 'datetime',
        'is_system_admin' => 'boolean',
        'locked' => 'boolean',
        'lock_expiry' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    // Check for permission
    public function hasPermission(string $permissionName): bool
    {
        // 1. Check role permissions
        $rolePermission = $this->role?->permissions()->where('name', $permissionName)->exists();

        // 2. Check user-specific permissions
        $userPermission = $this->permissions()->where('name', $permissionName)->exists();

        return $rolePermission || $userPermission;
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function identityMemberships(): HasMany
    {
        return $this->hasMany(IdentityProjectMembership::class, 'user_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification((string) $token));
    }
}
