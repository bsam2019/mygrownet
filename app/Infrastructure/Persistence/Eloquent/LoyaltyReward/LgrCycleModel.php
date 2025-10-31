<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class LgrCycleModel extends Model
{
    protected $table = 'lgr_cycles';

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'status',
        'active_days',
        'total_earned_lgc',
        'daily_rate',
        'suspension_reason',
        'suspended_at',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active_days' => 'integer',
        'total_earned_lgc' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'suspended_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LgrActivityModel::class, 'lgr_cycle_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(LgrPayoutModel::class, 'lgr_cycle_id');
    }
}
