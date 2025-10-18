<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Traits\HasPermissions;

class Role extends Model implements RoleContract
{
    use HasPermissions;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'guard_name'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id')
            ->where('model_type', User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public static function findByName(string $name, ?string $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        return static::where('name', $name)->where('guard_name', $guardName)->firstOrFail();
    }

    public static function findById(string|int $id, ?string $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        return static::where('id', $id)->where('guard_name', $guardName)->firstOrFail();
    }

    public static function findOrCreate(string $name, ?string $guardName = null): RoleContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        return static::firstOrCreate([
            'name' => $name,
            'guard_name' => $guardName,
        ], [
            'slug' => Str::slug($name),
        ]);
    }
}
