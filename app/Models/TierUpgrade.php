<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierUpgrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_tier_id',
        'to_tier_id',
        'total_investment_amount',
        'team_volume',
        'active_referrals',
        'achievement_bonus_awarded',
        'consecutive_months_qualified',
        'upgrade_reason',
        'processed_at'
    ];

    protected $casts = [
        'total_investment_amount' => 'decimal:2',
        'team_volume' => 'decimal:2',
        'achievement_bonus_awarded' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the user who was upgraded
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tier the user was upgraded from
     */
    public function fromTier(): BelongsTo
    {
        return $this->belongsTo(InvestmentTier::class, 'from_tier_id');
    }

    /**
     * Get the tier the user was upgraded to
     */
    public function toTier(): BelongsTo
    {
        return $this->belongsTo(InvestmentTier::class, 'to_tier_id');
    }
}