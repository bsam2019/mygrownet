<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SitePermission extends Model
{
    protected $table = 'site_permissions';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'group_name',
        'description',
    ];

    // Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            SiteRole::class,
            'site_role_permissions',
            'site_permission_id',
            'site_role_id'
        );
    }

    // Scopes
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group_name', $group);
    }

    // Static helper to get all permissions grouped
    public static function getAllGrouped(): array
    {
        return static::all()
            ->groupBy('group_name')
            ->toArray();
    }
}
