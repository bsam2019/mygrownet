<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LgrDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lgr_cycle_id',
        'distribution_date',
        'daily_bp',
        'tier_multiplier',
        'weighted_bp',
        'lgr_amount',
        'daily_cap',
        'capped',
    ];

    protected $casts = [
        'distribution_date' => 'date',
        'tier_multiplier' => 'decimal:2',
        'weighted_bp' => 'decimal:2',
        'lgr_amount' => 'decimal:2',
        'daily_cap' => 'decimal:2',
        'capped' => 'boolean',
    ];

    /**
     * Get the user that owns the distribution
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the LGR cycle
     */
    public function lgrCycle(): BelongsTo
    {
        return $this->belongsTo(LgrCycle::class);
    }

    /**
     * Scope for today's distributions
     */
    public function scopeToday($query)
    {
        return $query->where('distribution_date', now()->toDateString());
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('distribution_date', $date);
    }

    /**
     * Get total LGR earned by user in date range
     */
    public static function totalForUserInRange(int $userId, $startDate, $endDate): float
    {
        return self::where('user_id', $userId)
            ->whereBetween('distribution_date', [$startDate, $endDate])
            ->sum('lgr_amount');
    }

    /**
     * Get total LGR distributed on a date
     */
    public static function totalForDate($date): float
    {
        return self::where('distribution_date', $date)->sum('lgr_amount');
    }
}
