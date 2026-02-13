<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CmsUserModel extends Model
{
    protected $table = 'cms_users';

    protected $fillable = [
        'company_id',
        'user_id',
        'role_id',
        'employee_number',
        'status',
        'last_login_at',
        'tour_completed',
        'tour_progress',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'tour_completed' => 'boolean',
        'tour_progress' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(RoleModel::class, 'role_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role?->hasPermission($permission) ?? false;
    }

    public function canApprove(string $type, float $amount = 0): bool
    {
        return $this->role?->canApprove($type, $amount) ?? false;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}
