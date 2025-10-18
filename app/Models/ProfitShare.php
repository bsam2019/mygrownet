<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ProfitShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'profit_distribution_id',
        'amount',
        'status',
        'payment_date',
        'reference_number',
        // Community project enhancements
        'tier_at_distribution',
        'tier_bonus_applied',
        'distribution_type',
        'base_amount',
        'bonus_amount',
        'community_project_bonus',
        'includes_community_allocation',
        'payment_method',
        'processed_at',
        'processed_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'base_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'community_project_bonus' => 'decimal:2',
        'tier_bonus_applied' => 'decimal:2',
        'includes_community_allocation' => 'boolean',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function profitDistribution(): BelongsTo
    {
        return $this->belongsTo(ProfitDistribution::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'calculated');
    }

    public function scopeForTier(Builder $query, string $tier): Builder
    {
        return $query->where('tier_at_distribution', $tier);
    }

    public function scopeQuarterly(Builder $query): Builder
    {
        return $query->where('distribution_type', 'quarterly');
    }

    public function scopeAnnual(Builder $query): Builder
    {
        return $query->where('distribution_type', 'annual');
    }

    // Business Logic Methods
    public function markAsPaid(User $processedBy, string $paymentMethod = 'internal_balance'): void
    {
        if ($this->status !== 'calculated') {
            throw new \Exception('Only calculated profit shares can be marked as paid.');
        }

        $this->update([
            'status' => 'paid',
            'processed_at' => now(),
            'processed_by' => $processedBy->id,
            'payment_method' => $paymentMethod,
            'reference_number' => $this->reference_number ?? $this->generateReferenceNumber()
        ]);

        // Create transaction record
        Transaction::create([
            'user_id' => $this->investment->user_id,
            'investment_id' => $this->investment_id,
            'amount' => $this->amount,
            'transaction_type' => 'profit_share',
            'status' => 'completed',
            'payment_method' => $paymentMethod,
            'description' => $this->getPaymentDescription(),
            'reference_number' => $this->reference_number,
            'processed_at' => now(),
            'processed_by' => $processedBy->id
        ]);
    }

    public function calculateTierBonus(): float
    {
        if (!$this->tier_bonus_applied || $this->tier_bonus_applied <= 1.0) {
            return 0;
        }

        $baseAmount = $this->base_amount ?? ($this->amount / $this->tier_bonus_applied);
        return $this->amount - $baseAmount;
    }

    public function getEffectiveROI(): float
    {
        if (!$this->investment || $this->investment->amount <= 0) {
            return 0;
        }

        return ($this->amount / $this->investment->amount) * 100;
    }

    public function getTierBonusPercentage(): float
    {
        if (!$this->tier_bonus_applied) {
            return 0;
        }

        return ($this->tier_bonus_applied - 1.0) * 100;
    }

    public function isEligibleForCommunityBonus(): bool
    {
        return $this->includes_community_allocation && 
               in_array($this->tier_at_distribution, ['Gold', 'Diamond', 'Elite']);
    }

    public function applyCommunityProjectBonus(float $bonusAmount): void
    {
        if (!$this->isEligibleForCommunityBonus()) {
            throw new \Exception('Profit share is not eligible for community project bonus.');
        }

        $this->update([
            'community_project_bonus' => $bonusAmount,
            'amount' => $this->amount + $bonusAmount
        ]);
    }

    private function generateReferenceNumber(): string
    {
        return 'PS-' . $this->id . '-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function getPaymentDescription(): string
    {
        $description = ucfirst($this->distribution_type) . ' profit share';
        
        if ($this->tier_at_distribution) {
            $description .= " ({$this->tier_at_distribution} tier)";
        }

        if ($this->tier_bonus_applied > 1.0) {
            $bonusPercentage = ($this->tier_bonus_applied - 1.0) * 100;
            $description .= " with {$bonusPercentage}% tier bonus";
        }

        return $description;
    }

    public function getProfitShareSummary(): array
    {
        return [
            'profit_share_id' => $this->id,
            'investment_id' => $this->investment_id,
            'user_name' => $this->investment->user->name ?? 'Unknown',
            'amount' => $this->amount,
            'base_amount' => $this->base_amount,
            'bonus_amount' => $this->calculateTierBonus(),
            'community_project_bonus' => $this->community_project_bonus,
            'tier_at_distribution' => $this->tier_at_distribution,
            'tier_bonus_percentage' => $this->getTierBonusPercentage(),
            'distribution_type' => $this->distribution_type,
            'effective_roi' => $this->getEffectiveROI(),
            'status' => $this->status,
            'payment_date' => $this->payment_date,
            'processed_at' => $this->processed_at,
            'reference_number' => $this->reference_number
        ];
    }

    // Static methods
    public static function createFromInvestment(
        Investment $investment,
        ProfitDistribution $distribution,
        float $amount,
        float $tierBonus = 1.0
    ): self {
        $tierName = $investment->tier?->name ?? 'Bronze';
        $baseAmount = $tierBonus > 1.0 ? ($amount / $tierBonus) : $amount;

        return self::create([
            'investment_id' => $investment->id,
            'profit_distribution_id' => $distribution->id,
            'amount' => $amount,
            'base_amount' => $baseAmount,
            'bonus_amount' => $amount - $baseAmount,
            'tier_at_distribution' => $tierName,
            'tier_bonus_applied' => $tierBonus,
            'distribution_type' => $distribution->period_type,
            'includes_community_allocation' => $distribution->includes_community_projects,
            'status' => 'calculated',
            'payment_date' => now()->addDays(3)
        ]);
    }

    public static function getTierDistributionSummary(string $tier): array
    {
        $profitShares = self::forTier($tier)->get();
        
        return [
            'tier' => $tier,
            'total_shares' => $profitShares->count(),
            'total_amount' => $profitShares->sum('amount'),
            'total_base_amount' => $profitShares->sum('base_amount'),
            'total_bonus_amount' => $profitShares->sum('bonus_amount'),
            'total_community_bonus' => $profitShares->sum('community_project_bonus'),
            'paid_shares' => $profitShares->where('status', 'paid')->count(),
            'paid_amount' => $profitShares->where('status', 'paid')->sum('amount'),
            'pending_shares' => $profitShares->where('status', 'calculated')->count(),
            'pending_amount' => $profitShares->where('status', 'calculated')->sum('amount'),
            'average_share' => $profitShares->count() > 0 ? $profitShares->avg('amount') : 0
        ];
    }
}