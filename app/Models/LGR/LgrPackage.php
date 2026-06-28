<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LgrPackage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'package_amount',
        'daily_lgr_rate',
        'duration_days',
        'total_reward',
        'is_active',
        'sort_order',
        'description',
        'features',
    ];

    protected $casts = [
        'package_amount' => 'decimal:2',
        'daily_lgr_rate' => 'decimal:2',
        'duration_days' => 'integer',
        'total_reward' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'features' => 'array',
    ];

    /**
     * Get active packages ordered by sort_order
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('package_amount')
            ->get();
    }

    /**
     * Calculate if total_reward matches daily_rate * duration
     */
    public function isRewardCalculationCorrect(): bool
    {
        $calculated = $this->daily_lgr_rate * $this->duration_days;
        return abs($calculated - $this->total_reward) < 0.01; // Allow for floating point precision
    }

    /**
     * Get ROI percentage
     */
    public function getRoiPercentage(): float
    {
        if ($this->package_amount == 0) {
            return 0;
        }
        return ($this->total_reward / $this->package_amount) * 100;
    }

    /**
     * Get formatted package amount
     */
    public function getFormattedPackageAmountAttribute(): string
    {
        return 'K' . number_format($this->package_amount, 2);
    }

    /**
     * Get formatted daily rate
     */
    public function getFormattedDailyRateAttribute(): string
    {
        return 'K' . number_format($this->daily_lgr_rate, 2);
    }

    /**
     * Get formatted total reward
     */
    public function getFormattedTotalRewardAttribute(): string
    {
        return 'K' . number_format($this->total_reward, 2);
    }

    /**
     * Users who have purchased this package
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'lgr_package_id');
    }
}
