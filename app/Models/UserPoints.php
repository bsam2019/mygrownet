<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPoints extends Model
{
    protected $fillable = [
        'user_id',
        'lifetime_points',
        'monthly_points',
        'last_month_points',
        'three_month_average',
        'current_streak_months',
        'longest_streak_months',
        'active_multiplier',
        'last_activity_date',
    ];

    protected $casts = [
        'lifetime_points' => 'integer',
        'monthly_points' => 'integer',
        'last_month_points' => 'integer',
        'three_month_average' => 'decimal:2',
        'current_streak_months' => 'integer',
        'longest_streak_months' => 'integer',
        'active_multiplier' => 'decimal:2',
        'last_activity_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Check if user meets monthly qualification
     */
    public function meetsMonthlyQualification(): bool
    {
        $requiredMap = $this->getRequiredMapForLevel();
        return $this->monthly_points >= $requiredMap;
    }

    /**
     * Get required MAP based on user's professional level
     */
    public function getRequiredMapForLevel(): int
    {
        return match ($this->user->current_professional_level) {
            'associate' => 100,
            'professional' => 200,
            'senior' => 300,
            'manager' => 400,
            'director' => 500,
            'executive' => 600,
            'ambassador' => 800,
            default => 100,
        };
    }

    /**
     * Get performance tier based on monthly points
     */
    public function getPerformanceTier(): string
    {
        return match (true) {
            $this->monthly_points >= 1000 => 'platinum',
            $this->monthly_points >= 600 => 'gold',
            $this->monthly_points >= 300 => 'silver',
            default => 'bronze',
        };
    }

    /**
     * Get commission bonus percentage based on performance tier
     */
    public function getCommissionBonusPercent(): float
    {
        return match ($this->getPerformanceTier()) {
            'platinum' => 30.0,
            'gold' => 20.0,
            'silver' => 10.0,
            default => 0.0,
        };
    }

    /**
     * Update multiplier based on streak
     */
    public function updateMultiplier(): void
    {
        $multiplier = match (true) {
            $this->current_streak_months >= 12 => 1.50,
            $this->current_streak_months >= 6 => 1.25,
            $this->current_streak_months >= 3 => 1.10,
            default => 1.00,
        };

        $this->update(['active_multiplier' => $multiplier]);
    }
}
