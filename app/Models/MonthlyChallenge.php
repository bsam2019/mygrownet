<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyChallenge extends Model
{
    protected $fillable = [
        'month',
        'year',
        'challenge_name',
        'description',
        'point_multiplier',
        'target_activity',
        'is_active',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'point_multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active challenges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('year', now()->year)
            ->where('month', now()->month);
    }

    /**
     * Get formatted period
     */
    public function getFormattedPeriodAttribute(): string
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}
