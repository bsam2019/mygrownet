<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InvestmentOpportunity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'minimum_investment',
        'expected_returns',
        'status',
        'category_id',
        'duration',
        'risk_level',
        'created_by',
        'updated_by',
        // Community project enhancements
        'community_project_id',
        'is_community_project',
        'project_type',
        'requires_community_voting',
        'voting_threshold_percentage',
        'minimum_voters_required',
        'voting_criteria',
        'governance_rules',
        'required_membership_tiers',
        'minimum_community_contribution',
        'maximum_community_contribution',
        'tier_contribution_limits',
        'tier_voting_weights',
        'community_voting_start',
        'community_voting_end',
        'funding_deadline',
        'project_milestones',
        'success_metrics',
        'total_community_votes',
        'community_approval_rating',
        'community_contributors_count',
        'community_funding_raised',
        'community_profit_share_percentage',
        'includes_quarterly_distributions',
        'includes_annual_distributions',
        'profit_distribution_rules'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'minimum_investment' => 'decimal:2',
        'expected_returns' => 'decimal:2',
        'duration' => 'integer',
        'risk_level' => 'string',
        // Community project casts
        'is_community_project' => 'boolean',
        'requires_community_voting' => 'boolean',
        'voting_threshold_percentage' => 'decimal:2',
        'voting_criteria' => 'array',
        'governance_rules' => 'array',
        'required_membership_tiers' => 'array',
        'minimum_community_contribution' => 'decimal:2',
        'maximum_community_contribution' => 'decimal:2',
        'tier_contribution_limits' => 'array',
        'tier_voting_weights' => 'array',
        'community_voting_start' => 'date',
        'community_voting_end' => 'date',
        'funding_deadline' => 'date',
        'project_milestones' => 'array',
        'success_metrics' => 'array',
        'community_approval_rating' => 'decimal:2',
        'community_funding_raised' => 'decimal:2',
        'community_profit_share_percentage' => 'decimal:2',
        'includes_quarterly_distributions' => 'boolean',
        'includes_annual_distributions' => 'boolean',
        'profit_distribution_rules' => 'array'
    ];

    protected $attributes = [
        'voting_criteria' => '[]',
        'governance_rules' => '[]',
        'required_membership_tiers' => '[]',
        'tier_contribution_limits' => '[]',
        'tier_voting_weights' => '[]',
        'project_milestones' => '[]',
        'success_metrics' => '[]',
        'profit_distribution_rules' => '[]'
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(InvestmentCategory::class);
    }

    public function communityProject(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class, 'opportunity_id');
    }

    public function communityInvestments(): HasMany
    {
        return $this->hasMany(Investment::class, 'opportunity_id')
                    ->where('is_community_investment', true);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(InvestmentOpportunityVote::class);
    }

    public function communityDistributions(): HasMany
    {
        return $this->hasMany(CommunityInvestmentDistribution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeCommunityProjects(Builder $query): Builder
    {
        return $query->where('is_community_project', true);
    }

    public function scopeTraditionalInvestments(Builder $query): Builder
    {
        return $query->where('is_community_project', false);
    }

    public function scopeRequiresVoting(Builder $query): Builder
    {
        return $query->where('requires_community_voting', true);
    }

    public function scopeVotingActive(Builder $query): Builder
    {
        return $query->where('requires_community_voting', true)
                    ->where('community_voting_start', '<=', now())
                    ->where('community_voting_end', '>=', now());
    }

    public function scopeForTier(Builder $query, string $tierName): Builder
    {
        return $query->where(function ($q) use ($tierName) {
            $q->whereJsonContains('required_membership_tiers', $tierName)
              ->orWhereJsonLength('required_membership_tiers', 0);
        });
    }

    // Business Logic Methods
    public function isAccessibleByTier(string $tierName): bool
    {
        if (empty($this->required_membership_tiers)) {
            return true;
        }

        return in_array($tierName, $this->required_membership_tiers);
    }

    public function isAccessibleByUser(User $user): bool
    {
        if ($user->currentTier) {
            return $this->isAccessibleByTier($user->currentTier->name);
        }

        return false;
    }

    public function isVotingActive(): bool
    {
        if (!$this->requires_community_voting) {
            return false;
        }

        return now()->between($this->community_voting_start, $this->community_voting_end);
    }

    public function canUserVote(User $user): bool
    {
        if (!$this->isVotingActive()) {
            return false;
        }

        if (!$this->isAccessibleByUser($user)) {
            return false;
        }

        // Check if user has already voted
        return !$this->votes()->where('user_id', $user->id)->exists();
    }

    public function getUserVotingPower(User $user): float
    {
        $tierName = $user->currentTier?->name ?? 'Bronze';
        $tierWeights = $this->tier_voting_weights ?? [];
        $baseWeight = $tierWeights[$tierName] ?? 1.0;

        // Get user's investment amount in this opportunity
        $investmentAmount = $this->investments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->sum('amount');

        // Calculate voting power based on investment amount and tier weight
        $totalInvestmentPool = $this->investments()->where('status', 'active')->sum('amount');
        
        if ($totalInvestmentPool <= 0) {
            return $baseWeight;
        }

        $investmentPercentage = $investmentAmount / $totalInvestmentPool;
        return $baseWeight * (1 + $investmentPercentage);
    }

    public function getVotingResults(): array
    {
        $votes = $this->votes()->with('user')->get();
        
        $results = [
            'total_votes' => $votes->count(),
            'approve_votes' => $votes->where('vote_type', 'approve')->count(),
            'reject_votes' => $votes->where('vote_type', 'reject')->count(),
            'abstain_votes' => $votes->where('vote_type', 'abstain')->count(),
            'total_voting_power' => $votes->sum('voting_power'),
            'approve_voting_power' => $votes->where('vote_type', 'approve')->sum('voting_power'),
            'reject_voting_power' => $votes->where('vote_type', 'reject')->sum('voting_power'),
            'abstain_voting_power' => $votes->where('vote_type', 'abstain')->sum('voting_power')
        ];

        $results['approval_percentage'] = $results['total_voting_power'] > 0 
            ? ($results['approve_voting_power'] / $results['total_voting_power']) * 100 
            : 0;

        $results['meets_threshold'] = $results['approval_percentage'] >= $this->voting_threshold_percentage;
        $results['meets_minimum_voters'] = $results['total_votes'] >= $this->minimum_voters_required;
        $results['is_approved'] = $results['meets_threshold'] && $results['meets_minimum_voters'];

        return $results;
    }

    public function processVotingResults(): array
    {
        if (!$this->requires_community_voting) {
            return ['status' => 'no_voting_required', 'approved' => true];
        }

        if ($this->isVotingActive()) {
            return ['status' => 'voting_in_progress', 'approved' => false];
        }

        $results = $this->getVotingResults();
        
        if ($results['is_approved']) {
            $this->update(['status' => 'approved']);
            return ['status' => 'approved', 'approved' => true, 'results' => $results];
        } else {
            $this->update(['status' => 'rejected']);
            return ['status' => 'rejected', 'approved' => false, 'results' => $results];
        }
    }

    public function getContributionLimitForTier(string $tierName): ?float
    {
        $limits = $this->tier_contribution_limits ?? [];
        return $limits[$tierName] ?? $this->maximum_community_contribution;
    }

    public function canUserContribute(User $user, float $amount): bool
    {
        if (!$this->isAccessibleByUser($user)) {
            return false;
        }

        if ($amount < $this->minimum_community_contribution) {
            return false;
        }

        $tierName = $user->currentTier?->name ?? 'Bronze';
        $contributionLimit = $this->getContributionLimitForTier($tierName);
        
        if ($contributionLimit && $amount > $contributionLimit) {
            return false;
        }

        // Check if funding deadline has passed
        if ($this->funding_deadline && now()->gt($this->funding_deadline)) {
            return false;
        }

        return true;
    }

    public function addCommunityInvestment(User $user, float $amount): Investment
    {
        if (!$this->canUserContribute($user, $amount)) {
            throw new \Exception('User cannot contribute to this investment opportunity.');
        }

        return DB::transaction(function () use ($user, $amount) {
            $investment = Investment::create([
                'user_id' => $user->id,
                'opportunity_id' => $this->id,
                'community_project_id' => $this->community_project_id,
                'amount' => $amount,
                'is_community_investment' => true,
                'investment_source' => 'community_project',
                'community_contribution_amount' => $amount,
                'tier_at_community_investment' => $user->currentTier?->name ?? 'Bronze',
                'voting_power_weight' => $this->getUserVotingPower($user),
                'eligible_for_community_profits' => true,
                'community_investment_date' => now(),
                'status' => 'active'
            ]);

            // Update community funding tracking
            $this->increment('community_funding_raised', $amount);
            $this->increment('community_contributors_count');

            return $investment;
        });
    }

    public function calculateCommunityProfitDistribution(float $totalProfitPool): array
    {
        if (!$this->is_community_project || $this->community_profit_share_percentage <= 0) {
            return [];
        }

        $communityAllocation = ($totalProfitPool * $this->community_profit_share_percentage) / 100;
        $communityInvestments = $this->communityInvestments()->where('status', 'active')->get();
        $totalCommunityInvestment = $communityInvestments->sum('community_contribution_amount');

        $distributions = [];

        foreach ($communityInvestments as $investment) {
            if ($totalCommunityInvestment <= 0) {
                continue;
            }

            $investmentPercentage = $investment->community_contribution_amount / $totalCommunityInvestment;
            $baseShare = $communityAllocation * $investmentPercentage;

            // Apply tier bonus
            $tierMultiplier = $this->getTierBonusMultiplier($investment->tier_at_community_investment);
            $tierBonus = $baseShare * ($tierMultiplier - 1);

            // Apply voting participation bonus
            $votingBonus = 0;
            if ($investment->participated_in_voting) {
                $votingBonus = $baseShare * 0.05; // 5% bonus for voting participation
            }

            $totalShare = $baseShare + $tierBonus + $votingBonus;

            $distributions[] = [
                'investment_id' => $investment->id,
                'user_id' => $investment->user_id,
                'base_share' => $baseShare,
                'tier_bonus' => $tierBonus,
                'voting_bonus' => $votingBonus,
                'total_share' => $totalShare,
                'tier' => $investment->tier_at_community_investment,
                'investment_amount' => $investment->community_contribution_amount
            ];
        }

        return $distributions;
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

    public function getOpportunityStatistics(): array
    {
        return [
            'total_investments' => $this->investments()->count(),
            'community_investments' => $this->communityInvestments()->count(),
            'total_funding_raised' => $this->investments()->sum('amount'),
            'community_funding_raised' => $this->community_funding_raised,
            'community_contributors' => $this->community_contributors_count,
            'voting_statistics' => $this->requires_community_voting ? $this->getVotingResults() : null,
            'is_voting_active' => $this->isVotingActive(),
            'funding_deadline' => $this->funding_deadline,
            'days_until_deadline' => $this->funding_deadline ? now()->diffInDays($this->funding_deadline, false) : null
        ];
    }

    public static function createCommunityOpportunity(array $data): self
    {
        return self::create(array_merge($data, [
            'is_community_project' => true,
            'project_type' => 'community',
            'requires_community_voting' => true,
            'voting_threshold_percentage' => 60.0,
            'minimum_voters_required' => 5,
            'includes_quarterly_distributions' => true,
            'community_profit_share_percentage' => 20.0
        ]));
    }
} 