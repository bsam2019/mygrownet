<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class LgrPayoutModel extends Model
{
    protected $table = 'lgr_payouts';

    protected $fillable = [
        'user_id',
        'lgr_cycle_id',
        'lgr_pool_id',
        'payout_date',
        'lgc_amount',
        'pool_balance_before',
        'pool_balance_after',
        'proportional_adjustment',
        'adjustment_factor',
        'status',
        'notes',
    ];

    protected $casts = [
        'payout_date' => 'date',
        'lgc_amount' => 'decimal:2',
        'pool_balance_before' => 'decimal:2',
        'pool_balance_after' => 'decimal:2',
        'proportional_adjustment' => 'boolean',
        'adjustment_factor' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(LgrCycleModel::class, 'lgr_cycle_id');
    }

    public function pool(): BelongsTo
    {
        return $this->belongsTo(LgrPoolModel::class, 'lgr_pool_id');
    }
}
