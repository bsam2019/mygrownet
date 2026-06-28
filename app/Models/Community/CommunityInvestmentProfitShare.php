<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CommunityInvestmentProfitShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_investment_distribution_id',
        'investment_id',
        'user_id',
        'investment_opportunity_id',
        'investment_amount',
        'community_contribution_amount',
        'tier_at_distribution',
        'tier_bonus_multiplier',
        'voting_participation_bonus',
        'base_profit_share',
        'tier_bonus_amount',
        'participation_bonus_amount',
        'total_profit_share',
        'payment_status',
        'payment_method',
        'payment_date',
        'payment_reference',
        'payment_notes'
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'community_contribution_amount' => 'decimal:2',
        'tier_bonus_multiplier' => 'decimal:2',
        'voting_participation_bonus' => 'decimal:2',
        'base_profit_share' => 'decimal:2',
        'tier_bonus_amount' => 'decimal:2',
        'participation_bonus_amount' => 'decimal:2',
        'total_profit_share' => 'decimal:2',
        'payment_date' => 'datetime'
    ];

    // Relationships
    public function communityInvestmentDistribution(): BelongsTo
    {
        return $this->belongsTo(CommunityInvestmentDistribution::class);
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('payment_status', 'processed');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('payment_status', 'failed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('payment_status', 'cancelled');
    }

    public function scopeByTier(Builder $query, string $tier): Builder
    {
        return $query->where('tier_at_distribution', $tier);
    }

    public function scopeWithVotingBonus(Builder $query): Builder
    {
        return $query->where('voting_participation_bonus', '>', 0);
    }

    public function scopeByPaymentMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    // Business Logic Methods
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isProcessed(): bool
    {
        return $this->payment_status === 'processed';
    }

    public function isFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    public function isCancelled(): bool
    {
        return $this->payment_status === 'cancelled';
    }

    public function hasVotingBonus(): bool
    {
        return $this->voting_participation_bonus > 0;
    }

    public function hasTierBonus(): bool
    {
        return $this->tier_bonus_amount > 0;
    }

    public function getTotalBonusAmount(): float
    {
        return $this->tier_bonus_amount + $this->participation_bonus_amount;
    }

    public function getBonusPercentage(): float
    {
        if ($this->base_profit_share <= 0) {
            return 0;
        }

        return ($this->getTotalBonusAmount() / $this->base_profit_share) * 100;
    }

    public function getEffectiveROI(): float
    {
        if ($this->investment_amount <= 0) {
            return 0;
        }

        return ($this->total_profit_share / $this->investment_amount) * 100;
    }

    public function markAsProcessed(string $paymentReference, string $paymentMethod = 'internal_balance'): void
    {
        $this->update([
            'payment_status' => 'processed',
            'payment_date' => now(),
            'payment_reference' => $paymentReference,
            'payment_method' => $paymentMethod
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'payment_status' => 'failed',
            'payment_notes' => $reason
        ]);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'payment_status' => 'cancelled',
            'payment_notes' => $reason
        ]);
    }

    public function retry(): void
    {
        if (!$this->isFailed()) {
            throw new \Exception('Can only retry failed payments');
        }

        $this->update([
            'payment_status' => 'pending',
            'payment_notes' => null
        ]);
    }

    public function getProfitShareBreakdown(): array
    {
        return [
            'base_share' => [
                'amount' => $this->base_profit_share,
                'percentage' => 100.0
            ],
            'tier_bonus' => [
                'amount' => $this->tier_bonus_amount,
                'multiplier' => $this->tier_bonus_multiplier,
                'tier' => $this->tier_at_distribution
            ],
            'voting_bonus' => [
                'amount' => $this->participation_bonus_amount,
                'percentage' => $this->voting_participation_bonus * 100,
                'earned' => $this->hasVotingBonus()
            ],
            'total' => [
                'amount' => $this->total_profit_share,
                'bonus_percentage' => $this->getBonusPercentage(),
                'effective_roi' => $this->getEffectiveROI()
            ]
        ];
    }

    public function getPaymentDetails(): array
    {
        return [
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_date' => $this->payment_date,
            'payment_reference' => $this->payment_reference,
            'payment_notes' => $this->payment_notes,
            'is_processed' => $this->isProcessed(),
            'is_pending' => $this->isPending(),
            'is_failed' => $this->isFailed()
        ];
    }

    public function getShareSummary(): array
    {
        return [
            'share_id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? 'Unknown',
            'investment_amount' => $this->investment_amount,
            'tier_at_distribution' => $this->tier_at_distribution,
            'profit_share_breakdown' => $this->getProfitShareBreakdown(),
            'payment_details' => $this->getPaymentDetails(),
            'distribution_period' => $this->communityInvestmentDistribution->distribution_period_label ?? 'Unknown'
        ];
    }

    public static function calculateUserTotalShares(int $userId, ?string $distributionType = null): array
    {
        $query = self::where('user_id', $userId);
        
        if ($distributionType) {
            $query->whereHas('communityInvestmentDistribution', function ($q) use ($distributionType) {
                $q->where('distribution_type', $distributionType);
            });
        }

        $shares = $query->get();

        return [
            'total_shares' => $shares->count(),
            'total_amount' => $shares->sum('total_profit_share'),
            'total_base_amount' => $shares->sum('base_profit_share'),
            'total_bonuses' => $shares->sum('tier_bonus_amount') + $shares->sum('participation_bonus_amount'),
            'processed_shares' => $shares->where('payment_status', 'processed')->count(),
            'processed_amount' => $shares->where('payment_status', 'processed')->sum('total_profit_share'),
            'pending_shares' => $shares->where('payment_status', 'pending')->count(),
            'pending_amount' => $shares->where('payment_status', 'pending')->sum('total_profit_share'),
            'failed_shares' => $shares->where('payment_status', 'failed')->count(),
            'failed_amount' => $shares->where('payment_status', 'failed')->sum('total_profit_share')
        ];
    }

    public static function getDistributionStatistics(?string $distributionType = null): array
    {
        $query = self::query();
        
        if ($distributionType) {
            $query->whereHas('communityInvestmentDistribution', function ($q) use ($distributionType) {
                $q->where('distribution_type', $distributionType);
            });
        }

        $shares = $query->get();

        return [
            'total_shares' => $shares->count(),
            'total_amount_distributed' => $shares->sum('total_profit_share'),
            'total_base_amount' => $shares->sum('base_profit_share'),
            'total_tier_bonuses' => $shares->sum('tier_bonus_amount'),
            'total_voting_bonuses' => $shares->sum('participation_bonus_amount'),
            'processed_count' => $shares->where('payment_status', 'processed')->count(),
            'processed_amount' => $shares->where('payment_status', 'processed')->sum('total_profit_share'),
            'pending_count' => $shares->where('payment_status', 'pending')->count(),
            'pending_amount' => $shares->where('payment_status', 'pending')->sum('total_profit_share'),
            'failed_count' => $shares->where('payment_status', 'failed')->count(),
            'failed_amount' => $shares->where('payment_status', 'failed')->sum('total_profit_share'),
            'average_share_amount' => $shares->count() > 0 ? $shares->avg('total_profit_share') : 0,
            'tier_distribution' => $shares->groupBy('tier_at_distribution')->map(function ($tierShares) {
                return [
                    'count' => $tierShares->count(),
                    'total_amount' => $tierShares->sum('total_profit_share'),
                    'average_amount' => $tierShares->avg('total_profit_share')
                ];
            })->toArray()
        ];
    }
}