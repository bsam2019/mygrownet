<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalRewardAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'physical_reward_id',
        'tier_id',
        'team_volume_at_allocation',
        'active_referrals_at_allocation',
        'team_depth_at_allocation',
        'status',
        'allocated_at',
        'delivered_at',
        'ownership_transferred_at',
        'forfeited_at',
        'maintenance_compliant',
        'maintenance_months_completed',
        'last_maintenance_check',
        'maintenance_notes',
        'total_income_generated',
        'monthly_income_average',
        'income_tracking_started',
        'asset_management_details',
        'asset_manager',
        'special_conditions'
    ];

    protected $casts = [
        'team_volume_at_allocation' => 'decimal:2',
        'total_income_generated' => 'decimal:2',
        'monthly_income_average' => 'decimal:2',
        'asset_management_details' => 'array',
        'maintenance_compliant' => 'boolean',
        'allocated_at' => 'datetime',
        'delivered_at' => 'datetime',
        'ownership_transferred_at' => 'datetime',
        'forfeited_at' => 'datetime',
        'last_maintenance_check' => 'datetime',
        'income_tracking_started' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function physicalReward(): BelongsTo
    {
        return $this->belongsTo(PhysicalReward::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(InvestmentTier::class, 'tier_id');
    }

    /**
     * Check if allocation is eligible for ownership transfer
     */
    public function isEligibleForOwnershipTransfer(): bool
    {
        if ($this->status !== 'delivered') {
            return false;
        }

        $reward = $this->physicalReward;
        if (!$reward->requires_performance_maintenance) {
            return true; // Immediate ownership
        }

        return $this->maintenance_months_completed >= $reward->maintenance_period_months &&
               $this->maintenance_compliant;
    }

    /**
     * Transfer ownership to user
     */
    public function transferOwnership(): bool
    {
        if (!$this->isEligibleForOwnershipTransfer()) {
            return false;
        }

        $this->update([
            'status' => 'ownership_transferred',
            'ownership_transferred_at' => now()
        ]);

        // Record activity
        $this->user->recordActivity(
            'asset_ownership_transferred',
            "Ownership of {$this->physicalReward->name} transferred after completing maintenance requirements"
        );

        return true;
    }

    /**
     * Mark allocation as delivered
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        // Start income tracking if applicable
        if ($this->physicalReward->income_generating) {
            $this->update(['income_tracking_started' => now()]);
        }

        $this->user->recordActivity(
            'physical_reward_delivered',
            "Physical reward '{$this->physicalReward->name}' has been delivered"
        );
    }

    /**
     * Update maintenance status
     */
    public function updateMaintenanceStatus(bool $compliant, ?string $notes = null): void
    {
        $this->update([
            'maintenance_compliant' => $compliant,
            'last_maintenance_check' => now(),
            'maintenance_notes' => $notes
        ]);

        if ($compliant) {
            $this->increment('maintenance_months_completed');
        } else {
            // Reset maintenance months if not compliant
            $this->update(['maintenance_months_completed' => 0]);
        }
    }

    /**
     * Record income generated from asset
     */
    public function recordIncomeGenerated(float $amount): void
    {
        $this->increment('total_income_generated', $amount);
        
        // Update monthly average
        $monthsSinceStart = $this->income_tracking_started 
            ? now()->diffInMonths($this->income_tracking_started) + 1 
            : 1;
        
        $this->update([
            'monthly_income_average' => $this->total_income_generated / $monthsSinceStart
        ]);

        $this->user->recordActivity(
            'asset_income_recorded',
            "Income of K{$amount} recorded from {$this->physicalReward->name}"
        );
    }

    /**
     * Forfeit allocation due to performance issues
     */
    public function forfeit(string $reason): void
    {
        $this->update([
            'status' => 'forfeited',
            'forfeited_at' => now(),
            'maintenance_notes' => $reason
        ]);

        // Return to available pool
        $this->physicalReward->decrement('allocated_quantity');

        $this->user->recordActivity(
            'physical_reward_forfeited',
            "Physical reward '{$this->physicalReward->name}' forfeited: {$reason}"
        );
    }

    /**
     * Recover asset from user
     */
    public function recover(string $reason): void
    {
        $this->update([
            'status' => 'recovered',
            'maintenance_notes' => $reason
        ]);

        // Return to available pool
        $this->physicalReward->decrement('allocated_quantity');

        $this->user->recordActivity(
            'physical_reward_recovered',
            "Physical reward '{$this->physicalReward->name}' recovered: {$reason}"
        );
    }

    /**
     * Get allocation progress
     */
    public function getProgress(): array
    {
        $reward = $this->physicalReward;
        
        return [
            'status' => $this->status,
            'allocated_at' => $this->allocated_at,
            'delivered' => !is_null($this->delivered_at),
            'ownership_transferred' => !is_null($this->ownership_transferred_at),
            'maintenance_required' => $reward->requires_performance_maintenance,
            'maintenance_progress' => [
                'months_completed' => $this->maintenance_months_completed,
                'months_required' => $reward->maintenance_period_months,
                'percentage' => $reward->maintenance_period_months > 0 
                    ? ($this->maintenance_months_completed / $reward->maintenance_period_months) * 100 
                    : 100,
                'compliant' => $this->maintenance_compliant
            ],
            'income_generation' => [
                'enabled' => $reward->income_generating,
                'total_generated' => $this->total_income_generated,
                'monthly_average' => $this->monthly_income_average,
                'estimated_monthly' => $reward->estimated_monthly_income
            ]
        ];
    }

    /**
     * Scope for specific status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for maintenance due
     */
    public function scopeMaintenanceDue($query)
    {
        return $query->where('status', 'delivered')
                    ->where('maintenance_compliant', true)
                    ->where(function ($q) {
                        $q->whereNull('last_maintenance_check')
                          ->orWhere('last_maintenance_check', '<', now()->subMonth());
                    });
    }

    /**
     * Scope for ownership transfer eligible
     */
    public function scopeEligibleForOwnershipTransfer($query)
    {
        return $query->where('status', 'delivered')
                    ->where('maintenance_compliant', true)
                    ->whereHas('physicalReward', function ($q) {
                        $q->where('requires_performance_maintenance', true);
                    })
                    ->whereRaw('maintenance_months_completed >= (SELECT maintenance_period_months FROM physical_rewards WHERE id = physical_reward_allocations.physical_reward_id)');
    }
}