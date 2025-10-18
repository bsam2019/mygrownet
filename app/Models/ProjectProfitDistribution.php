<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ProjectProfitDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_project_id',
        'user_id',
        'project_contribution_id',
        'distribution_amount',
        'contribution_amount',
        'contribution_percentage',
        'project_profit_amount',
        'distribution_rate',
        'distribution_type',
        'period_start',
        'period_end',
        'distribution_period_label',
        'status',
        'calculated_at',
        'approved_at',
        'paid_at',
        'cancelled_at',
        'approved_by',
        'paid_by',
        'payment_reference',
        'payment_method',
        'notes',
        'tier_at_distribution',
        'tier_bonus_multiplier'
    ];

    protected $casts = [
        'distribution_amount' => 'decimal:2',
        'contribution_amount' => 'decimal:2',
        'contribution_percentage' => 'decimal:4',
        'project_profit_amount' => 'decimal:2',
        'distribution_rate' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'tier_bonus_multiplier' => 'decimal:2'
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class, 'community_project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(ProjectContribution::class, 'project_contribution_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Scopes
    public function scopeForProject(Builder $query, CommunityProject $project): Builder
    {
        return $query->where('community_project_id', $project->id);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('distribution_type', $type);
    }

    public function scopeForPeriod(Builder $query, string $periodLabel): Builder
    {
        return $query->where('distribution_period_label', $periodLabel);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['calculated', 'approved']);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    // Business Logic Methods
    public function approve(User $approvedBy): void
    {
        if ($this->status !== 'calculated') {
            throw new \Exception('Only calculated distributions can be approved.');
        }

        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy->id
        ]);
    }

    public function markAsPaid(User $paidBy, string $paymentReference = null, string $paymentMethod = null): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved distributions can be marked as paid.');
        }

        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => $paidBy->id,
            'payment_reference' => $paymentReference,
            'payment_method' => $paymentMethod
        ]);

        // Update the contribution's total returns
        $this->contribution->increment('total_returns_received', $this->distribution_amount);

        // Create transaction record
        Transaction::create([
            'user_id' => $this->user_id,
            'amount' => $this->distribution_amount,
            'transaction_type' => 'project_return',
            'status' => 'completed',
            'description' => "Profit distribution from {$this->project->name} - {$this->distribution_period_label}",
            'reference_number' => $paymentReference ?? 'PROJ-DIST-' . $this->id,
            'processed_at' => now()
        ]);
    }

    public function cancel(string $reason = null): void
    {
        if (!in_array($this->status, ['calculated', 'approved'])) {
            throw new \Exception('Cannot cancel this distribution.');
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'notes' => $reason
        ]);
    }

    public static function calculateDistribution(
        ProjectContribution $contribution,
        float $totalProjectProfit,
        string $distributionType,
        string $periodLabel,
        \Carbon\Carbon $periodStart,
        \Carbon\Carbon $periodEnd,
        float $distributionRate = null
    ): self {
        $project = $contribution->project;
        $user = $contribution->user;
        
        // Calculate contribution percentage of total project funding
        $contributionPercentage = $project->current_amount > 0 
            ? ($contribution->amount / $project->current_amount) * 100 
            : 0;

        // Calculate base distribution amount
        $baseDistribution = ($totalProjectProfit * $contributionPercentage) / 100;

        // Apply tier bonus multiplier
        $tierMultiplier = self::getTierBonusMultiplier($user->currentTier?->name ?? 'Bronze');
        $finalDistribution = $baseDistribution * $tierMultiplier;

        // Use provided rate or calculate from expected return
        $effectiveRate = $distributionRate ?? $project->expected_annual_return;

        return self::create([
            'community_project_id' => $project->id,
            'user_id' => $user->id,
            'project_contribution_id' => $contribution->id,
            'distribution_amount' => $finalDistribution,
            'contribution_amount' => $contribution->amount,
            'contribution_percentage' => $contributionPercentage,
            'project_profit_amount' => $totalProjectProfit,
            'distribution_rate' => $effectiveRate,
            'distribution_type' => $distributionType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'distribution_period_label' => $periodLabel,
            'status' => 'calculated',
            'calculated_at' => now(),
            'tier_at_distribution' => $user->currentTier?->name,
            'tier_bonus_multiplier' => $tierMultiplier
        ]);
    }

    public static function calculateProjectDistributions(
        CommunityProject $project,
        float $totalProfit,
        string $distributionType,
        string $periodLabel,
        \Carbon\Carbon $periodStart,
        \Carbon\Carbon $periodEnd
    ): array {
        $distributions = [];
        
        // Get all confirmed contributions for the project
        $contributions = $project->contributions()->confirmed()->get();
        
        foreach ($contributions as $contribution) {
            // Check if distribution already exists for this period
            $existingDistribution = self::where('project_contribution_id', $contribution->id)
                ->where('distribution_period_label', $periodLabel)
                ->where('distribution_type', $distributionType)
                ->first();

            if (!$existingDistribution) {
                $distribution = self::calculateDistribution(
                    $contribution,
                    $totalProfit,
                    $distributionType,
                    $periodLabel,
                    $periodStart,
                    $periodEnd
                );
                
                $distributions[] = $distribution;
            }
        }

        return $distributions;
    }

    public static function getTierBonusMultiplier(string $tierName): float
    {
        return match ($tierName) {
            'Bronze' => 1.0,
            'Silver' => 1.05, // 5% bonus
            'Gold' => 1.10,   // 10% bonus
            'Diamond' => 1.15, // 15% bonus
            'Elite' => 1.20,   // 20% bonus
            default => 1.0
        };
    }

    public function getDistributionSummary(): array
    {
        return [
            'distribution_id' => $this->id,
            'project_name' => $this->project->name,
            'distribution_amount' => $this->distribution_amount,
            'distribution_type' => $this->distribution_type,
            'period_label' => $this->distribution_period_label,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'contribution_amount' => $this->contribution_amount,
            'contribution_percentage' => $this->contribution_percentage,
            'distribution_rate' => $this->distribution_rate,
            'tier_at_distribution' => $this->tier_at_distribution,
            'tier_bonus_multiplier' => $this->tier_bonus_multiplier,
            'status' => $this->status,
            'calculated_at' => $this->calculated_at,
            'approved_at' => $this->approved_at,
            'paid_at' => $this->paid_at,
            'payment_reference' => $this->payment_reference
        ];
    }

    public static function getDistributionTypes(): array
    {
        return [
            'monthly' => 'Monthly Distribution',
            'quarterly' => 'Quarterly Distribution',
            'annual' => 'Annual Distribution',
            'final' => 'Final Distribution',
            'milestone' => 'Milestone Distribution'
        ];
    }

    public static function getUserDistributionSummary(User $user): array
    {
        $distributions = self::forUser($user)->with(['project', 'contribution'])->get();
        
        $summary = [
            'total_distributions' => $distributions->count(),
            'total_amount_received' => $distributions->where('status', 'paid')->sum('distribution_amount'),
            'pending_amount' => $distributions->whereIn('status', ['calculated', 'approved'])->sum('distribution_amount'),
            'by_status' => $distributions->groupBy('status')->map->count(),
            'by_type' => $distributions->groupBy('distribution_type')->map->count(),
            'by_project' => $distributions->groupBy('project.name')->map(function ($projectDistributions) {
                return [
                    'count' => $projectDistributions->count(),
                    'total_amount' => $projectDistributions->sum('distribution_amount'),
                    'paid_amount' => $projectDistributions->where('status', 'paid')->sum('distribution_amount')
                ];
            }),
            'recent_distributions' => $distributions->sortByDesc('calculated_at')->take(10)->values()
        ];

        return $summary;
    }
}