<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CommunityInvestmentDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_opportunity_id',
        'community_project_id',
        'profit_distribution_id',
        'distribution_period_label',
        'period_start',
        'period_end',
        'distribution_type',
        'total_profit_pool',
        'community_allocation_percentage',
        'community_allocation_amount',
        'total_distributed_amount',
        'status',
        'calculated_at',
        'approved_at',
        'distributed_at',
        'approved_by',
        'distributed_by',
        'distribution_criteria',
        'notes'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_profit_pool' => 'decimal:2',
        'community_allocation_percentage' => 'decimal:2',
        'community_allocation_amount' => 'decimal:2',
        'total_distributed_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'distributed_at' => 'datetime',
        'distribution_criteria' => 'array'
    ];

    protected $attributes = [
        'distribution_criteria' => '[]'
    ];

    // Relationships
    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }

    public function communityProject(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class);
    }

    public function profitDistribution(): BelongsTo
    {
        return $this->belongsTo(ProfitDistribution::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function profitShares(): HasMany
    {
        return $this->hasMany(CommunityInvestmentProfitShare::class);
    }

    // Scopes
    public function scopeQuarterly(Builder $query): Builder
    {
        return $query->where('distribution_type', 'quarterly');
    }

    public function scopeAnnual(Builder $query): Builder
    {
        return $query->where('distribution_type', 'annual');
    }

    public function scopeCalculated(Builder $query): Builder
    {
        return $query->where('status', 'calculated');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeDistributed(Builder $query): Builder
    {
        return $query->where('status', 'distributed');
    }

    public function scopeForPeriod(Builder $query, $startDate, $endDate): Builder
    {
        return $query->where('period_start', '>=', $startDate)
                    ->where('period_end', '<=', $endDate);
    }

    // Business Logic Methods
    public function isCalculated(): bool
    {
        return $this->status === 'calculated';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isDistributed(): bool
    {
        return $this->status === 'distributed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeApproved(): bool
    {
        return $this->isCalculated() && $this->community_allocation_amount > 0;
    }

    public function canBeDistributed(): bool
    {
        return $this->isApproved() && $this->profitShares()->count() > 0;
    }

    public function approve(User $approver): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now()
        ]);

        return true;
    }

    public function distribute(User $distributor): array
    {
        if (!$this->canBeDistributed()) {
            throw new \Exception('Distribution cannot be processed at this time.');
        }

        return DB::transaction(function () use ($distributor) {
            $results = [
                'processed_shares' => 0,
                'total_amount_distributed' => 0,
                'failed_payments' => 0,
                'errors' => []
            ];

            $profitShares = $this->profitShares()->where('payment_status', 'pending')->get();

            foreach ($profitShares as $share) {
                try {
                    // Process payment (this would integrate with actual payment system)
                    $paymentResult = $this->processPayment($share);
                    
                    if ($paymentResult['success']) {
                        $share->update([
                            'payment_status' => 'processed',
                            'payment_date' => now(),
                            'payment_reference' => $paymentResult['reference'],
                            'payment_method' => $paymentResult['method']
                        ]);

                        $results['processed_shares']++;
                        $results['total_amount_distributed'] += $share->total_profit_share;
                    } else {
                        $share->update([
                            'payment_status' => 'failed',
                            'payment_notes' => $paymentResult['error']
                        ]);

                        $results['failed_payments']++;
                        $results['errors'][] = "Payment failed for user {$share->user_id}: {$paymentResult['error']}";
                    }
                } catch (\Exception $e) {
                    $results['failed_payments']++;
                    $results['errors'][] = "Error processing payment for user {$share->user_id}: {$e->getMessage()}";
                }
            }

            // Update distribution status
            $this->update([
                'status' => 'distributed',
                'distributed_by' => $distributor->id,
                'distributed_at' => now(),
                'total_distributed_amount' => $results['total_amount_distributed']
            ]);

            return $results;
        });
    }

    private function processPayment(CommunityInvestmentProfitShare $share): array
    {
        // This would integrate with actual payment processing system
        // For now, simulate successful payment
        return [
            'success' => true,
            'reference' => 'CIP_' . $share->id . '_' . time(),
            'method' => 'internal_balance'
        ];
    }

    public function calculateProfitShares(): array
    {
        if ($this->community_allocation_amount <= 0) {
            return [];
        }

        $opportunity = $this->investmentOpportunity;
        $communityInvestments = $opportunity->communityInvestments()
            ->where('status', 'active')
            ->where('eligible_for_community_profits', true)
            ->get();

        $totalCommunityInvestment = $communityInvestments->sum('community_contribution_amount');
        
        if ($totalCommunityInvestment <= 0) {
            return [];
        }

        $profitShares = [];

        foreach ($communityInvestments as $investment) {
            $baseShare = ($investment->community_contribution_amount / $totalCommunityInvestment) * $this->community_allocation_amount;
            
            // Apply tier bonus
            $tierMultiplier = $this->getTierBonusMultiplier($investment->tier_at_community_investment);
            $tierBonus = $baseShare * ($tierMultiplier - 1);

            // Apply voting participation bonus
            $votingBonus = 0;
            if ($investment->participated_in_voting) {
                $votingBonus = $baseShare * 0.05; // 5% bonus
            }

            $totalShare = $baseShare + $tierBonus + $votingBonus;

            $profitShares[] = [
                'investment_id' => $investment->id,
                'user_id' => $investment->user_id,
                'investment_amount' => $investment->community_contribution_amount,
                'tier_at_distribution' => $investment->tier_at_community_investment,
                'tier_bonus_multiplier' => $tierMultiplier,
                'voting_participation_bonus' => $investment->participated_in_voting ? 0.05 : 0,
                'base_profit_share' => $baseShare,
                'tier_bonus_amount' => $tierBonus,
                'participation_bonus_amount' => $votingBonus,
                'total_profit_share' => $totalShare
            ];
        }

        return $profitShares;
    }

    public function createProfitShares(): int
    {
        $shareCalculations = $this->calculateProfitShares();
        $createdCount = 0;

        foreach ($shareCalculations as $calculation) {
            CommunityInvestmentProfitShare::create([
                'community_investment_distribution_id' => $this->id,
                'investment_id' => $calculation['investment_id'],
                'user_id' => $calculation['user_id'],
                'investment_opportunity_id' => $this->investment_opportunity_id,
                'investment_amount' => $calculation['investment_amount'],
                'community_contribution_amount' => $calculation['investment_amount'],
                'tier_at_distribution' => $calculation['tier_at_distribution'],
                'tier_bonus_multiplier' => $calculation['tier_bonus_multiplier'],
                'voting_participation_bonus' => $calculation['voting_participation_bonus'],
                'base_profit_share' => $calculation['base_profit_share'],
                'tier_bonus_amount' => $calculation['tier_bonus_amount'],
                'participation_bonus_amount' => $calculation['participation_bonus_amount'],
                'total_profit_share' => $calculation['total_profit_share'],
                'payment_status' => 'pending'
            ]);

            $createdCount++;
        }

        return $createdCount;
    }

    private function getTierBonusMultiplier(string $tierName): float
    {
        return match ($tierName) {
            'Bronze' => 1.0,
            'Silver' => 1.05,
            'Gold' => 1.10,
            'Diamond' => 1.15,
            'Elite' => 1.20,
            default => 1.0
        };
    }

    public function getDistributionSummary(): array
    {
        return [
            'distribution_id' => $this->id,
            'opportunity_name' => $this->investmentOpportunity->name,
            'period' => $this->distribution_period_label,
            'distribution_type' => $this->distribution_type,
            'total_profit_pool' => $this->total_profit_pool,
            'community_allocation_percentage' => $this->community_allocation_percentage,
            'community_allocation_amount' => $this->community_allocation_amount,
            'total_distributed_amount' => $this->total_distributed_amount,
            'status' => $this->status,
            'profit_shares_count' => $this->profitShares()->count(),
            'pending_payments' => $this->profitShares()->where('payment_status', 'pending')->count(),
            'processed_payments' => $this->profitShares()->where('payment_status', 'processed')->count(),
            'failed_payments' => $this->profitShares()->where('payment_status', 'failed')->count(),
            'calculated_at' => $this->calculated_at,
            'approved_at' => $this->approved_at,
            'distributed_at' => $this->distributed_at
        ];
    }

    public static function createDistribution(
        InvestmentOpportunity $opportunity,
        ProfitDistribution $profitDistribution,
        string $distributionType,
        string $periodLabel
    ): self {
        $communityAllocationPercentage = $opportunity->community_profit_share_percentage ?? 20;
        $communityAllocationAmount = ($profitDistribution->total_profit * $communityAllocationPercentage) / 100;

        $distribution = self::create([
            'investment_opportunity_id' => $opportunity->id,
            'community_project_id' => $opportunity->community_project_id,
            'profit_distribution_id' => $profitDistribution->id,
            'distribution_period_label' => $periodLabel,
            'period_start' => $profitDistribution->period_start,
            'period_end' => $profitDistribution->period_end,
            'distribution_type' => $distributionType,
            'total_profit_pool' => $profitDistribution->total_profit,
            'community_allocation_percentage' => $communityAllocationPercentage,
            'community_allocation_amount' => $communityAllocationAmount,
            'status' => 'calculated',
            'calculated_at' => now(),
            'distribution_criteria' => [
                'tier_bonus_enabled' => true,
                'voting_bonus_enabled' => true,
                'voting_bonus_percentage' => 5
            ]
        ]);

        // Create individual profit shares
        $distribution->createProfitShares();

        return $distribution;
    }
}