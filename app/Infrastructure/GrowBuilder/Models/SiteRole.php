<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteRole extends Model
{
    protected $table = 'site_roles';

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'is_system',
        'level',
        'type',
        'icon',
        'color',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'level' => 'integer',
    ];

    // Constants
    public const TYPE_STAFF = 'staff';
    public const TYPE_CLIENT = 'client';

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(SiteUser::class, 'site_role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            SitePermission::class,
            'site_role_permissions',
            'site_role_id',
            'site_permission_id'
        );
    }

    // Scopes
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeStaff($query)
    {
        return $query->where('type', self::TYPE_STAFF);
    }

    public function scopeClient($query)
    {
        return $query->where('type', self::TYPE_CLIENT);
    }

    // Helper methods
    public function isStaff(): bool
    {
        return $this->type === self::TYPE_STAFF;
    }

    public function isClient(): bool
    {
        return $this->type === self::TYPE_CLIENT;
    }
    public function hasPermission(string $permission): bool
    {
        // Admin role (level 100) has all permissions
        if ($this->level >= 100) {
            return true;
        }

        return $this->permissions()
            ->where('slug', $permission)
            ->exists();
    }

    public function grantPermission(SitePermission $permission): void
    {
        if (!$this->permissions()->where('site_permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
        }
    }

    public function revokePermission(SitePermission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}
