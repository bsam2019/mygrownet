<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'estimated_value',
        'required_membership_tiers',
        'required_referrals',
        'required_subscription_amount',
        'required_sustained_months',
        'required_team_volume',
        'required_team_depth',
        'maintenance_period_months',
        'requires_performance_maintenance',
        'income_generating',
        'estimated_monthly_income',
        'asset_management_options',
        'ownership_type',
        'ownership_conditions',
        'available_quantity',
        'allocated_quantity',
        'image_url',
        'specifications',
        'terms_and_conditions',
        'is_active'
    ];

    protected $casts = [
        'required_membership_tiers' => 'array',
        'estimated_value' => 'decimal:2',
        'required_subscription_amount' => 'decimal:2',
        'required_team_volume' => 'decimal:2',
        'estimated_monthly_income' => 'decimal:2',
        'asset_management_options' => 'array',
        'specifications' => 'array',
        'requires_performance_maintenance' => 'boolean',
        'income_generating' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function allocations(): HasMany
    {
        return $this->hasMany(PhysicalRewardAllocation::class);
    }

    /**
     * Check if reward is available for allocation
     */
    public function isAvailable(): bool
    {
        return $this->is_active && 
               $this->allocated_quantity < $this->available_quantity;
    }

    /**
     * Check if user is eligible for this reward
     */
    public function isEligibleForUser(User $user): bool
    {
        $tier = $user->membershipTier;
        if (!$tier) {
            return false;
        }

        // Check tier eligibility
        if (!in_array($tier->name, $this->required_membership_tiers)) {
            return false;
        }

        $teamVolume = $user->getCurrentTeamVolume();
        if (!$teamVolume) {
            return false;
        }

        // Check team volume requirements
        if ($teamVolume->team_volume < $this->required_team_volume) {
            return false;
        }

        // Check referral requirements
        if ($teamVolume->active_referrals_count < $this->required_referrals) {
            return false;
        }

        // Check team depth requirements
        if ($teamVolume->team_depth < $this->required_team_depth) {
            return false;
        }

        // Check subscription amount requirements
        if ($tier->monthly_fee < $this->required_subscription_amount) {
            return false;
        }

        // Check sustained months requirement
        if ($this->required_sustained_months > 0) {
            $consecutiveMonths = $this->getConsecutiveMonthsQualified($user);
            if ($consecutiveMonths < $this->required_sustained_months) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get consecutive months user has qualified for this reward
     */
    private function getConsecutiveMonthsQualified(User $user): int
    {
        // This would integrate with TierQualificationTrackingService
        // For now, return a basic calculation
        $tier = $user->membershipTier;
        if (!$tier) {
            return 0;
        }

        // Check tier upgrade history for consecutive months
        $tierUpgrade = $user->tierUpgrades()
            ->where('to_tier_id', $tier->id)
            ->latest()
            ->first();

        if (!$tierUpgrade) {
            return 0;
        }

        return now()->diffInMonths($tierUpgrade->created_at);
    }

    /**
     * Allocate reward to user
     */
    public function allocateToUser(User $user): ?PhysicalRewardAllocation
    {
        if (!$this->isAvailable() || !$this->isEligibleForUser($user)) {
            return null;
        }

        $allocation = PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $this->id,
            'tier_id' => $user->current_investment_tier_id,
            'team_volume_at_allocation' => $user->getCurrentTeamVolume()?->team_volume ?? 0,
            'active_referrals_at_allocation' => $user->getCurrentTeamVolume()?->active_referrals_count ?? 0,
            'status' => 'allocated',
            'allocated_at' => now()
        ]);

        $this->increment('allocated_quantity');

        return $allocation;
    }

    /**
     * Get rewards by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get active rewards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get available rewards (not fully allocated)
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('allocated_quantity < available_quantity');
    }

    /**
     * Get rewards for specific tier
     */
    public function scopeForTier($query, string $tierName)
    {
        return $query->whereJsonContains('required_membership_tiers', $tierName);
    }
}