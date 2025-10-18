<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierQualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tier_id',
        'qualification_month',
        'qualifies',
        'team_volume',
        'active_referrals',
        'required_team_volume',
        'required_active_referrals',
        'consecutive_months',
        'permanent_status',
        'permanent_achieved_at'
    ];

    protected $casts = [
        'qualification_month' => 'date',
        'qualifies' => 'boolean',
        'team_volume' => 'decimal:2',
        'required_team_volume' => 'decimal:2',
        'permanent_status' => 'boolean',
        'permanent_achieved_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(InvestmentTier::class, 'tier_id');
    }

    /**
     * Check if user qualifies for the tier based on current metrics
     */
    public function checkQualification(): bool
    {
        return $this->active_referrals >= $this->required_active_referrals &&
               $this->team_volume >= $this->required_team_volume;
    }

    /**
     * Update qualification status
     */
    public function updateQualificationStatus(): void
    {
        $this->qualifies = $this->checkQualification();
        $this->save();
    }

    /**
     * Get consecutive months qualified for this tier
     */
    public function getConsecutiveMonthsQualified(): int
    {
        if (!$this->qualifies) {
            return 0;
        }

        // Count consecutive months backwards from this month
        $consecutiveMonths = 1; // Current month
        $currentMonth = $this->qualification_month->copy()->subMonth();

        while ($currentMonth >= now()->subYear()) { // Check up to 1 year back
            $previousQualification = static::where('user_id', $this->user_id)
                ->where('tier_id', $this->tier_id)
                ->where('qualification_month', $currentMonth)
                ->where('qualifies', true)
                ->first();

            if (!$previousQualification) {
                break;
            }

            $consecutiveMonths++;
            $currentMonth = $currentMonth->subMonth();
        }

        return $consecutiveMonths;
    }

    /**
     * Update consecutive months count
     */
    public function updateConsecutiveMonths(): void
    {
        $this->consecutive_months = $this->getConsecutiveMonthsQualified();
        
        // Check if permanent status should be awarded
        $tier = $this->tier;
        if ($tier && $this->consecutive_months >= $tier->consecutive_months_required && !$this->permanent_status) {
            $this->permanent_status = true;
            $this->permanent_achieved_at = now();
        }
        
        $this->save();
    }

    /**
     * Get qualification shortfall
     */
    public function getQualificationShortfall(): array
    {
        return [
            'referrals_needed' => max(0, $this->required_active_referrals - $this->active_referrals),
            'team_volume_needed' => max(0, $this->required_team_volume - $this->team_volume),
            'qualifies' => $this->qualifies
        ];
    }

    /**
     * Scope for qualified records
     */
    public function scopeQualified($query)
    {
        return $query->where('qualifies', true);
    }

    /**
     * Scope for permanent status records
     */
    public function scopePermanentStatus($query)
    {
        return $query->where('permanent_status', true);
    }

    /**
     * Scope for specific month
     */
    public function scopeForMonth($query, $month)
    {
        return $query->where('qualification_month', $month);
    }

    /**
     * Scope for specific tier
     */
    public function scopeForTier($query, $tierId)
    {
        return $query->where('tier_id', $tierId);
    }
}