<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusChilimbaGroupModel extends Model
{
    protected $table = 'lifeplus_chilimba_groups';

    protected $fillable = [
        'user_id',
        'name',
        'meeting_frequency',
        'meeting_day',
        'meeting_time',
        'meeting_location',
        'contribution_amount',
        'total_members',
        'start_date',
        'user_role',
        'is_active',
    ];

    protected $casts = [
        'contribution_amount' => 'decimal:2',
        'start_date' => 'date',
        'meeting_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaMemberModel::class, 'group_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaContributionModel::class, 'group_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaPayoutModel::class, 'group_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaLoanModel::class, 'group_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaMeetingModel::class, 'group_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaAuditLogModel::class, 'group_id');
    }
}
