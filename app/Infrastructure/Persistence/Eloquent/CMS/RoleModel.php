<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoleModel extends Model
{
    protected $table = 'cms_roles';

    protected $fillable = [
        'company_id',
        'name',
        'permissions',
        'approval_authority',
        'is_system_role',
    ];

    protected $casts = [
        'permissions' => 'array',
        'approval_authority' => 'array',
        'is_system_role' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(CmsUserModel::class, 'role_id');
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        
        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check for exact permission
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check for wildcard module permission (e.g., 'jobs.*')
        $parts = explode('.', $permission);
        if (count($parts) > 1) {
            $moduleWildcard = $parts[0] . '.*';
            if (in_array($moduleWildcard, $permissions)) {
                return true;
            }
        }

        return false;
    }

    public function canApprove(string $type, float $amount = 0): bool
    {
        $authority = $this->approval_authority ?? [];
        
        if (!isset($authority[$type])) {
            return false;
        }

        $limit = $authority[$type];
        
        // If limit is true, can approve any amount
        if ($limit === true) {
            return true;
        }

        // If limit is numeric, check against amount
        if (is_numeric($limit)) {
            return $amount <= $limit;
        }

        return false;
    }
}
