<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'guard_name'
    ];

    protected $casts = [
        'guard_name' => 'string'
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_permissions', 'permission_id', 'model_id')
            ->where('model_type', User::class);
    }

    public static function findByName(string $name, ?string $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        return static::where('name', $name)->where('guard_name', $guardName)->firstOrFail();
    }

    public static function findById(string|int $id, ?string $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        return static::where('id', $id)->where('guard_name', $guardName)->firstOrFail();
    }

    public static function findOrCreate(string $name, ?string $guardName = null): PermissionContract
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
