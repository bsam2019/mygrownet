<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveTypeModel extends Model
{
    protected $table = 'cms_leave_types';

    protected $fillable = [
        'company_id',
        'leave_type_name',
        'leave_code',
        'description',
        'default_days_per_year',
        'is_paid',
        'requires_approval',
        'can_carry_forward',
        'max_carry_forward_days',
        'max_consecutive_days',
        'min_notice_days',
        'is_active',
    ];

    protected $casts = [
        'default_days_per_year' => 'integer',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'can_carry_forward' => 'boolean',
        'max_carry_forward_days' => 'integer',
        'max_consecutive_days' => 'integer',
        'min_notice_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalanceModel::class, 'leave_type_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequestModel::class, 'leave_type_id');
    }
}
