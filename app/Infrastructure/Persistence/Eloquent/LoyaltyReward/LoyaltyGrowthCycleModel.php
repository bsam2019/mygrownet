<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyGrowthCycleModel extends Model
{
    protected $table = 'loyalty_growth_cycles';

    protected $fillable = [
        'cycle_id',
        'user_id',
        'start_date',
        'end_date',
        'status',
        'active_days',
        'earned_amount',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'completed_at' => 'datetime',
        'active_days' => 'integer',
        'earned_amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LoyaltyActivityModel::class, 'cycle_id');
    }
}
