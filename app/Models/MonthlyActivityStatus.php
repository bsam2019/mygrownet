<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyActivityStatus extends Model
{
    protected $table = 'monthly_activity_status';

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'map_earned',
        'map_required',
        'qualified',
        'performance_tier',
        'commission_bonus_percent',
        'team_synergy_bonus',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'map_earned' => 'integer',
        'map_required' => 'integer',
        'qualified' => 'boolean',
        'commission_bonus_percent' => 'decimal:2',
        'team_synergy_bonus' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Scope for qualified members
     */
    public function scopeQualified($query)
    {
        return $query->where('qualified', true);
    }

    /**
     * Get formatted month-year
     */
    public function getFormattedPeriodAttribute(): string
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}
