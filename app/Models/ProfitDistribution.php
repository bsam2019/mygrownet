<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitDistribution extends Model
{
    protected $fillable = [
        'period_type',
        'period_start',
        'period_end',
        'total_profit',
        'distribution_percentage',
        'total_distributed',
        'status',
        'notes',
        'created_by',
        'processed_by',
        'processed_at',
        // Community project enhancements
        'community_project_allocation',
        'community_project_percentage',
        'tier_based_bonuses',
        'distribution_source',
        'includes_community_projects',
        'community_profit_pool',
        'tier_bonus_multipliers'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_profit' => 'decimal:2',
        'distribution_percentage' => 'decimal:2',
        'total_distributed' => 'decimal:2',
        'processed_at' => 'datetime',
        // Community project casts
        'community_project_allocation' => 'decimal:2',
        'community_project_percentage' => 'decimal:2',
        'tier_based_bonuses' => 'decimal:2',
        'includes_community_projects' => 'boolean',
        'community_profit_pool' => 'decimal:2',
        'tier_bonus_multipliers' => 'array'
    ];

    protected $attributes = [
        'tier_bonus_multipliers' => '[]'
    ];

    /**
     * Get the user who created this distribution
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who processed this distribution
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get individual profit shares for this distribution
     */
    public function profitShares(): HasMany
    {
        return $this->hasMany(ProfitShare::class);
    }

    /**
     * Get community project profit distributions
     */
    public function communityProjectDistributions(): HasMany
    {
        return $this->hasMany(ProjectProfitDistribution::class, 'period_start', 'period_start')
                    ->where('period_end', $this->period_end);
    }

    // Scopes
    public function scopeQuarterly(Builder $query): Builder
    {
        return $query->where('period_type', 'quarterly');
    }

    public function scopeAnnual(Builder $query): Builder
    {
        return $query->where('period_type', 'annual');
    }

    public function scopeWithCommunityProjects(Builder $query): Builder
    {
        return $query->where('includes_community_projects', true);
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', 'processed');
    }

    // Business Logic Methods
    public function calculateCommunityProjectAllocation(): float
    {
        if (!$this->includes_community_projects) {
            return 0;
        }

        $allocationPercentage = $this->community_project_percentage ?? 20; // Default 20%
        return ($this->total_profit * $allocationPercentage) / 100;
    }

    public function calculateTierBasedDistribution(): array
    {
        $tierAllocations = [];
        $totalInvestmentPool = $this->getTotalInvestmentPool();
        $availableProfit = $this->total_profit - $this->calculateCommunityProjectAllocation();

        // Get tier-based multipliers
        $multipliers = $this->tier_bonus_multipliers ?: $this->getDefaultTierMultipliers();

        foreach (['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'] as $tier) {
            $tierInvestments = $this->getTierInvestmentTotal($tier);
            $baseAllocation = $tierInvestments > 0 
                ? ($availableProfit * ($tierInvestments / $totalInvestmentPool))
                : 0;
            
            $multiplier = $multipliers[$tier] ?? 1.0;
            $tierAllocations[$tier] = [
                'base_allocation' => $baseAllocation,
                'multiplier' => $multiplier,
                'final_allocation' => $baseAllocation * $multiplier,
                'tier_investment_total' => $tierInvestments
            ];
        }

        return $tierAllocations;
    }

    public function processDistribution(User $processedBy): array
    {
        if ($this->status === 'processed') {
            throw new \Exception('Distribution has already been processed.');
        }

        return DB::transaction(function () use ($processedBy) {
            $results = [
                'individual_distributions' => [],
                'community_project_distributions' => [],
                'total_distributed' => 0
            ];

            // Process individual investment profit shares
            $individualResults = $this->processIndividualDistributions();
            $results['individual_distributions'] = $individualResults;
            $results['total_distributed'] += collect($individualResults)->sum('amount');

            // Process community project distributions if enabled
            if ($this->includes_community_projects) {
                $communityResults = $this->processCommunityProjectDistributions();
                $results['community_project_distributions'] = $communityResults;
                $results['total_distributed'] += collect($communityResults)->sum('distribution_amount');
            }

            // Update distribution status
            $this->update([
                'status' => 'processed',
                'processed_by' => $processedBy->id,
                'processed_at' => now(),
                'total_distributed' => $results['total_distributed']
            ]);

            return $results;
        });
    }

    private function processIndividualDistributions(): array
    {
        $distributions = [];
        $tierAllocations = $this->calculateTierBasedDistribution();

        foreach ($tierAllocations as $tier => $allocation) {
            if ($allocation['final_allocation'] <= 0) {
                continue;
            }

            // Get active investments for this tier
            $investments = Investment::active()
                ->whereHas('tier', function ($query) use ($tier) {
                    $query->where('name', $tier);
                })
                ->get();

            foreach ($investments as $investment) {
                $userShare = $this->calculateUserProfitShare($investment, $allocation);
                
                if ($userShare > 0) {
                    $profitShare = ProfitShare::create([
                        'investment_id' => $investment->id,
                        'profit_distribution_id' => $this->id,
                        'amount' => $userShare,
                        'tier_at_distribution' => $tier,
                        'tier_bonus_applied' => $allocation['multiplier'],
                        'distribution_type' => $this->period_type,
                        'status' => 'calculated',
                        'payment_date' => now()->addDays(3) // 3 days processing time
                    ]);

                    $distributions[] = $profitShare;
                }
            }
        }

        return $distributions;
    }

    private function processCommunityProjectDistributions(): array
    {
        $communityAllocation = $this->calculateCommunityProjectAllocation();
        
        if ($communityAllocation <= 0) {
            return [];
        }

        // Get active community projects
        $activeProjects = CommunityProject::where('status', 'active')
            ->where('current_amount', '>', 0)
            ->get();

        $distributions = [];
        $totalProjectValue = $activeProjects->sum('current_amount');

        foreach ($activeProjects as $project) {
            if ($totalProjectValue <= 0) {
                continue;
            }

            // Calculate project's share of community allocation
            $projectShare = ($communityAllocation * ($project->current_amount / $totalProjectValue));
            
            // Create distributions for project contributors
            $projectDistributions = ProjectProfitDistribution::calculateProjectDistributions(
                $project,
                $projectShare,
                $this->period_type,
                $this->generatePeriodLabel(),
                $this->period_start,
                $this->period_end
            );

            $distributions = array_merge($distributions, $projectDistributions);
        }

        return $distributions;
    }

    private function calculateUserProfitShare(Investment $investment, array $tierAllocation): float
    {
        $tierInvestmentTotal = $tierAllocation['tier_investment_total'];
        
        if ($tierInvestmentTotal <= 0) {
            return 0;
        }

        $userPercentage = $investment->amount / $tierInvestmentTotal;
        return $tierAllocation['final_allocation'] * $userPercentage;
    }

    private function getTotalInvestmentPool(): float
    {
        return Investment::active()->sum('amount');
    }

    private function getTierInvestmentTotal(string $tierName): float
    {
        return Investment::active()
            ->whereHas('tier', function ($query) use ($tierName) {
                $query->where('name', $tierName);
            })
            ->sum('amount');
    }

    private function getDefaultTierMultipliers(): array
    {
        return [
            'Bronze' => 1.0,
            'Silver' => 1.05,  // 5% bonus
            'Gold' => 1.10,    // 10% bonus
            'Diamond' => 1.15, // 15% bonus
            'Elite' => 1.20    // 20% bonus
        ];
    }

    private function generatePeriodLabel(): string
    {
        return match ($this->period_type) {
            'monthly' => $this->period_start->format('F Y'),
            'quarterly' => 'Q' . $this->period_start->quarter . ' ' . $this->period_start->year,
            'annual' => $this->period_start->year,
            default => $this->period_start->format('Y-m-d')
        };
    }

    // Static methods for creating distributions
    public static function createQuarterlyDistribution(
        float $totalProfit,
        float $communityProjectPercentage = 20,
        bool $includeCommunityProjects = true,
        User $createdBy = null
    ): self {
        $quarterStart = now()->startOfQuarter();
        $quarterEnd = now()->endOfQuarter();

        return self::create([
            'period_type' => 'quarterly',
            'period_start' => $quarterStart,
            'period_end' => $quarterEnd,
            'total_profit' => $totalProfit,
            'distribution_percentage' => 100, // Distribute all profit
            'community_project_percentage' => $communityProjectPercentage,
            'includes_community_projects' => $includeCommunityProjects,
            'community_project_allocation' => $includeCommunityProjects 
                ? ($totalProfit * $communityProjectPercentage / 100) 
                : 0,
            'status' => 'calculated',
            'created_by' => $createdBy?->id,
            'tier_bonus_multipliers' => [
                'Bronze' => 1.0,
                'Silver' => 1.05,
                'Gold' => 1.10,
                'Diamond' => 1.15,
                'Elite' => 1.20
            ]
        ]);
    }

    public static function createAnnualDistribution(
        float $totalProfit,
        float $communityProjectPercentage = 25,
        bool $includeCommunityProjects = true,
        User $createdBy = null
    ): self {
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();

        return self::create([
            'period_type' => 'annual',
            'period_start' => $yearStart,
            'period_end' => $yearEnd,
            'total_profit' => $totalProfit,
            'distribution_percentage' => 100,
            'community_project_percentage' => $communityProjectPercentage,
            'includes_community_projects' => $includeCommunityProjects,
            'community_project_allocation' => $includeCommunityProjects 
                ? ($totalProfit * $communityProjectPercentage / 100) 
                : 0,
            'status' => 'calculated',
            'created_by' => $createdBy?->id,
            'tier_bonus_multipliers' => [
                'Bronze' => 1.0,
                'Silver' => 1.08,  // Higher bonuses for annual
                'Gold' => 1.15,
                'Diamond' => 1.22,
                'Elite' => 1.30
            ]
        ]);
    }

    public function getDistributionSummary(): array
    {
        return [
            'distribution_id' => $this->id,
            'period_type' => $this->period_type,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'total_profit' => $this->total_profit,
            'total_distributed' => $this->total_distributed,
            'community_project_allocation' => $this->community_project_allocation,
            'community_project_percentage' => $this->community_project_percentage,
            'includes_community_projects' => $this->includes_community_projects,
            'tier_bonus_multipliers' => $this->tier_bonus_multipliers,
            'status' => $this->status,
            'processed_at' => $this->processed_at,
            'individual_shares_count' => $this->profitShares()->count(),
            'community_distributions_count' => $this->communityProjectDistributions()->count()
        ];
    }
}
