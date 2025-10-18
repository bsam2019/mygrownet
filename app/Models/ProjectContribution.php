<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ProjectContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'community_project_id',
        'amount',
        'status',
        'tier_at_contribution',
        'contributed_at',
        'confirmed_at',
        'cancelled_at',
        'transaction_reference',
        'payment_method',
        'payment_details',
        'total_returns_received',
        'expected_annual_return',
        'auto_reinvest',
        'notes',
        'confirmed_by',
        'cancelled_by',
        'cancellation_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contributed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_details' => 'array',
        'total_returns_received' => 'decimal:2',
        'expected_annual_return' => 'decimal:2',
        'auto_reinvest' => 'boolean'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class, 'community_project_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForProject(Builder $query, CommunityProject $project): Builder
    {
        return $query->where('community_project_id', $project->id);
    }

    // Business Logic Methods
    public function confirm(User $confirmedBy = null): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Only pending contributions can be confirmed.');
        }

        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $confirmedBy?->id
        ]);

        // Update project funding progress
        $this->project->updateFundingProgress($this->amount);
    }

    public function cancel(User $cancelledBy = null, string $reason = null): void
    {
        if (!in_array($this->status, ['pending', 'confirmed'])) {
            throw new \Exception('Cannot cancel this contribution.');
        }

        $wasConfirmed = $this->status === 'confirmed';

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy?->id,
            'cancellation_reason' => $reason
        ]);

        // If was confirmed, adjust project funding
        if ($wasConfirmed) {
            $this->project->decrement('current_amount', $this->amount);
            $this->project->decrement('total_contributors');
        }
    }

    public function refund(User $refundedBy = null, string $reason = null): void
    {
        if ($this->status !== 'confirmed') {
            throw new \Exception('Only confirmed contributions can be refunded.');
        }

        $this->update([
            'status' => 'refunded',
            'cancelled_at' => now(),
            'cancelled_by' => $refundedBy?->id,
            'cancellation_reason' => $reason
        ]);

        // Adjust project funding
        $this->project->decrement('current_amount', $this->amount);
        $this->project->decrement('total_contributors');
    }

    public function calculateExpectedReturns(): array
    {
        $project = $this->project;
        $returnRate = $this->expected_annual_return ?? $project->expected_annual_return;
        
        $annualReturn = ($this->amount * $returnRate) / 100;
        $monthlyReturn = $annualReturn / 12;
        $totalReturn = $annualReturn * ($project->project_duration_months / 12);

        return [
            'contribution_amount' => $this->amount,
            'return_rate' => $returnRate,
            'expected_annual_return' => $annualReturn,
            'expected_monthly_return' => $monthlyReturn,
            'expected_total_return' => $totalReturn,
            'project_duration_months' => $project->project_duration_months,
            'roi_percentage' => ($totalReturn / $this->amount) * 100
        ];
    }

    public function getContributionPercentage(): float
    {
        if ($this->project->target_amount <= 0) {
            return 0;
        }

        return ($this->amount / $this->project->target_amount) * 100;
    }

    public function getVotingPower(): float
    {
        $tierWeights = [
            'Bronze' => 1.0,
            'Silver' => 1.2,
            'Gold' => 1.5,
            'Diamond' => 2.0,
            'Elite' => 3.0
        ];

        $tierWeight = $tierWeights[$this->tier_at_contribution] ?? 1.0;
        $contributionPercentage = $this->getContributionPercentage();

        return $tierWeight * $contributionPercentage;
    }

    public function addReturn(float $amount, string $type = 'profit_distribution'): void
    {
        $this->increment('total_returns_received', $amount);

        // Create transaction record
        Transaction::create([
            'user_id' => $this->user_id,
            'amount' => $amount,
            'transaction_type' => $type,
            'status' => 'completed',
            'description' => "Return from project: {$this->project->name}",
            'reference_number' => 'PROJ-' . $this->project->id . '-' . $this->id . '-' . time(),
            'processed_at' => now()
        ]);
    }

    public function getReturnOnInvestment(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return ($this->total_returns_received / $this->amount) * 100;
    }

    public function isEligibleForReturns(): bool
    {
        return $this->status === 'confirmed' && 
               $this->project->status === 'active';
    }

    public function getContributionSummary(): array
    {
        return [
            'contribution_id' => $this->id,
            'project_name' => $this->project->name,
            'amount' => $this->amount,
            'status' => $this->status,
            'tier_at_contribution' => $this->tier_at_contribution,
            'contributed_at' => $this->contributed_at,
            'confirmed_at' => $this->confirmed_at,
            'contribution_percentage' => $this->getContributionPercentage(),
            'voting_power' => $this->getVotingPower(),
            'total_returns_received' => $this->total_returns_received,
            'roi_percentage' => $this->getReturnOnInvestment(),
            'expected_returns' => $this->calculateExpectedReturns(),
            'is_eligible_for_returns' => $this->isEligibleForReturns()
        ];
    }
}