<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamVolume extends Model
{
    protected $fillable = [
        'user_id',
        'personal_volume',
        'team_volume',
        'team_depth',
        'active_referrals_count',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'personal_volume' => 'decimal:2',
        'team_volume' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate performance bonus based on team volume
     */
    public function calculatePerformanceBonus(): float
    {
        $volume = $this->team_volume;
        
        if ($volume >= 100000) {
            return $volume * 0.10; // 10% for K100,000+
        } elseif ($volume >= 50000) {
            return $volume * 0.07; // 7% for K50,000+
        } elseif ($volume >= 25000) {
            return $volume * 0.05; // 5% for K25,000+
        } elseif ($volume >= 10000) {
            return $volume * 0.02; // 2% for K10,000+
        }
        
        return 0;
    }

    /**
     * Check if user qualifies for tier upgrade based on team volume
     */
    public function qualifiesForTierUpgrade(string $targetTier): bool
    {
        $requirements = [
            'Silver' => ['volume' => 5000, 'referrals' => 3],
            'Gold' => ['volume' => 15000, 'referrals' => 10],
            'Diamond' => ['volume' => 50000, 'referrals' => 25],
            'Elite' => ['volume' => 150000, 'referrals' => 50],
        ];

        if (!isset($requirements[$targetTier])) {
            return false;
        }

        $requirement = $requirements[$targetTier];
        
        return $this->team_volume >= $requirement['volume'] && 
               $this->active_referrals_count >= $requirement['referrals'];
    }
}