<?php

namespace App\Services\UserManagementService\Infrastructure\Models\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Role> $roles
 * @property-read Collection<int, User> $users
 */
class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',          // e.g. "create_user", "edit_article"
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Permission belongs to many Roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    /**
     * Permission belongs to many Users (user-specific permissions).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }

    /**
     * Expose the permission name for string casting/authorization matrix.
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
