<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'role_name',
        'is_system_role',
        'permissions_json',
        'description',
    ];

    protected $casts = [
        'permissions_json' => 'array',
        'is_system_role' => 'boolean',
    ];

    /**
     * Get the agency this role belongs to
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get users with this role
     */
    public function agencyUsers()
    {
        return $this->hasMany(AgencyUser::class, 'role_id');
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions_json ?? []);
    }

    /**
     * Check if role has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->permissions_json ?? []));
    }

    /**
     * Check if role has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return empty(array_diff($permissions, $this->permissions_json ?? []));
    }

    /**
     * Add a permission to the role
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions_json ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions_json = $permissions;
            $this->save();
        }
    }

    /**
     * Remove a permission from the role
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions_json ?? [];
        
        if (($key = array_search($permission, $permissions)) !== false) {
            unset($permissions[$key]);
            $this->permissions_json = array_values($permissions);
            $this->save();
        }
    }

    /**
     * Scope for system roles
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system_role', true);
    }

    /**
     * Scope for custom roles
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system_role', false);
    }
}
