<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'permission_key',
        'delegated_by',
        'requires_approval',
        'approval_manager_id',
        'is_active',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_by');
    }

    public function approvalManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approval_manager_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EmployeeDelegationLog::class, 'delegation_id');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(DelegationApprovalRequest::class, 'delegation_id');
    }

    /**
     * Check if delegation is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Scope for active delegations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for specific permission
     */
    public function scopeForPermission($query, string $permission)
    {
        return $query->where('permission_key', $permission);
    }
}
