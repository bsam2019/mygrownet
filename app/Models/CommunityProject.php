<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CommunityProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'detailed_description',
        'type',
        'category',
        'target_amount',
        'current_amount',
        'minimum_contribution',
        'maximum_contribution',
        'expected_annual_return',
        'project_duration_months',
        'funding_start_date',
        'funding_end_date',
        'project_start_date',
        'expected_completion_date',
        'actual_completion_date',
        'status',
        'risk_level',
        'requires_voting',
        'is_featured',
        'auto_approve_contributions',
        'required_membership_tiers',
        'tier_contribution_limits',
        'tier_voting_weights',
        'project_manager_id',
        'created_by',
        'project_milestones',
        'risk_factors',
        'success_metrics',
        'featured_image_url',
        'gallery_images',
        'documents',
        'total_contributors',
        'total_votes',
        'community_approval_rating'
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'minimum_contribution' => 'decimal:2',
        'maximum_contribution' => 'decimal:2',
        'expected_annual_return' => 'decimal:2',
        'funding_start_date' => 'date',
        'funding_end_date' => 'date',
        'project_start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'requires_voting' => 'boolean',
        'is_featured' => 'boolean',
        'auto_approve_contributions' => 'boolean',
        'required_membership_tiers' => 'array',
        'tier_contribution_limits' => 'array',
        'tier_voting_weights' => 'array',
        'project_milestones' => 'array',
        'risk_factors' => 'array',
        'success_metrics' => 'array',
        'gallery_images' => 'array',
        'documents' => 'array',
        'community_approval_rating' => 'decimal:2'
    ];

    protected $attributes = [
        'required_membership_tiers' => '[]',
        'tier_contribution_limits' => '[]',
        'tier_voting_weights' => '[]',
        'project_milestones' => '[]',
        'risk_factors' => '[]',
        'success_metrics' => '[]',
        'gallery_images' => '[]',
        'documents' => '[]'
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(ProjectContribution::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(ProjectInvestment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ProjectVote::class);
    }

    public function profitDistributions(): HasMany
    {
        return $this->hasMany(ProjectProfitDistribution::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['funding', 'active']);
    }

    public function scopeFunding(Builder $query): Builder
    {
        return $query->where('status', 'funding')
                    ->where('funding_start_date', '<=', now())
                    ->where('funding_end_date', '>=', now());
    }

    public function scopeForTier(Builder $query, string $tierName): Builder
    {
        return $query->where(function ($q) use ($tierName) {
            $q->whereJsonContains('required_membership_tiers', $tierName)
              ->orWhereJsonLength('required_membership_tiers', 0);
        });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByRiskLevel(Builder $query, string $riskLevel): Builder
    {
        return $query->where('risk_level', $riskLevel);
    }

    // Business Logic Methods
    public function isAccessibleByTier(string $tierName): bool
    {
        // If no specific tiers required, accessible to all
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

    public function canUserContribute(User $user): bool
    {
        return $this->status === 'funding' &&
               $this->isAccessibleByUser($user) &&
               $this->isFundingActive() &&
               !$this->hasUserReachedContributionLimit($user);
    }

    public function isFundingActive(): bool
    {
        return $this->status === 'funding' &&
               now()->between($this->funding_start_date, $this->funding_end_date) &&
               $this->current_amount < $this->target_amount;
    }

    public function getFundingProgress(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return min(($this->current_amount / $this->target_amount) * 100, 100);
    }

    public function getRemainingFundingAmount(): float
    {
        return max($this->target_amount - $this->current_amount, 0);
    }

    public function getDaysRemainingForFunding(): int
    {
        if ($this->status !== 'funding') {
            return 0;
        }

        return max(now()->diffInDays($this->funding_end_date, false), 0);
    }

    public function getContributionLimitForTier(string $tierName): ?float
    {
        $limits = $this->tier_contribution_limits ?? [];
        return $limits[$tierName] ?? $this->maximum_contribution;
    }

    public function getVotingWeightForTier(string $tierName): float
    {
        $weights = $this->tier_voting_weights ?? [];
        return $weights[$tierName] ?? 1.0;
    }

    public function hasUserReachedContributionLimit(User $user): bool
    {
        $tierName = $user->currentTier?->name ?? 'Bronze';
        $limit = $this->getContributionLimitForTier($tierName);
        
        if (!$limit) {
            return false;
        }

        $userTotalContribution = $this->contributions()
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->sum('amount');

        return $userTotalContribution >= $limit;
    }

    public function getUserContributionTotal(User $user): float
    {
        return $this->contributions()
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->sum('amount');
    }

    public function getUserVotingPower(User $user): float
    {
        $tierName = $user->currentTier?->name ?? 'Bronze';
        $baseWeight = $this->getVotingWeightForTier($tierName);
        $contributionAmount = $this->getUserContributionTotal($user);
        
        // Voting power = tier weight * contribution percentage
        $contributionPercentage = $this->target_amount > 0 
            ? $contributionAmount / $this->target_amount 
            : 0;

        return $baseWeight * $contributionPercentage * 100;
    }

    public function addContribution(User $user, float $amount, array $additionalData = []): ProjectContribution
    {
        if (!$this->canUserContribute($user)) {
            throw new \Exception('User cannot contribute to this project at this time.');
        }

        $tierName = $user->currentTier?->name ?? 'Bronze';
        $contributionLimit = $this->getContributionLimitForTier($tierName);
        $currentContribution = $this->getUserContributionTotal($user);

        if ($contributionLimit && ($currentContribution + $amount) > $contributionLimit) {
            throw new \Exception("Contribution would exceed tier limit of K{$contributionLimit}.");
        }

        if ($amount < $this->minimum_contribution) {
            throw new \Exception("Minimum contribution is K{$this->minimum_contribution}.");
        }

        return DB::transaction(function () use ($user, $amount, $additionalData, $tierName) {
            $contribution = $this->contributions()->create(array_merge([
                'user_id' => $user->id,
                'amount' => $amount,
                'tier_at_contribution' => $tierName,
                'status' => $this->auto_approve_contributions ? 'confirmed' : 'pending',
                'contributed_at' => now()
            ], $additionalData));

            if ($this->auto_approve_contributions) {
                $this->updateFundingProgress($amount);
            }

            return $contribution;
        });
    }

    public function updateFundingProgress(float $amount): void
    {
        $this->increment('current_amount', $amount);
        $this->increment('total_contributors');

        // Check if funding target is reached
        if ($this->current_amount >= $this->target_amount) {
            $this->update(['status' => 'active']);
        }
    }

    public function calculateExpectedReturns(User $user): array
    {
        $contribution = $this->getUserContributionTotal($user);
        $annualReturn = ($contribution * $this->expected_annual_return) / 100;
        $monthlyReturn = $annualReturn / 12;
        $totalReturn = $annualReturn * ($this->project_duration_months / 12);

        return [
            'contribution_amount' => $contribution,
            'expected_annual_return_rate' => $this->expected_annual_return,
            'expected_annual_return_amount' => $annualReturn,
            'expected_monthly_return' => $monthlyReturn,
            'expected_total_return' => $totalReturn,
            'project_duration_months' => $this->project_duration_months,
            'roi_percentage' => $contribution > 0 ? ($totalReturn / $contribution) * 100 : 0
        ];
    }

    public function getProjectStatistics(): array
    {
        return [
            'funding_progress' => $this->getFundingProgress(),
            'remaining_amount' => $this->getRemainingFundingAmount(),
            'days_remaining' => $this->getDaysRemainingForFunding(),
            'total_contributors' => $this->total_contributors,
            'average_contribution' => $this->total_contributors > 0 
                ? $this->current_amount / $this->total_contributors 
                : 0,
            'community_approval' => $this->community_approval_rating,
            'total_votes' => $this->total_votes,
            'risk_level' => $this->risk_level,
            'expected_roi' => $this->expected_annual_return
        ];
    }

    public function getProjectTimeline(): array
    {
        return [
            'funding_period' => [
                'start' => $this->funding_start_date,
                'end' => $this->funding_end_date,
                'is_active' => $this->isFundingActive()
            ],
            'project_period' => [
                'start' => $this->project_start_date,
                'expected_completion' => $this->expected_completion_date,
                'actual_completion' => $this->actual_completion_date,
                'duration_months' => $this->project_duration_months
            ],
            'milestones' => $this->project_milestones ?? []
        ];
    }

    public function canUserVote(User $user): bool
    {
        return $this->requires_voting &&
               $this->getUserContributionTotal($user) > 0 &&
               $this->isAccessibleByUser($user);
    }

    public static function getProjectTypes(): array
    {
        return [
            'real_estate' => 'Real Estate Development',
            'agriculture' => 'Agricultural Projects',
            'sme' => 'Small & Medium Enterprises',
            'digital' => 'Digital/Technology Projects',
            'infrastructure' => 'Community Infrastructure'
        ];
    }

    public static function getProjectCategories(): array
    {
        return [
            'property_development' => 'Property Development',
            'farming' => 'Farming & Agriculture',
            'business_venture' => 'Business Ventures',
            'technology' => 'Technology Solutions',
            'community_infrastructure' => 'Community Infrastructure'
        ];
    }

    public static function getRiskLevels(): array
    {
        return [
            'low' => 'Low Risk (5-8% expected return)',
            'medium' => 'Medium Risk (8-15% expected return)',
            'high' => 'High Risk (15%+ expected return)'
        ];
    }

    public static function getTierBasedAccessStructure(): array
    {
        return [
            'Bronze' => [
                'max_contribution' => 5000,
                'voting_weight' => 1.0,
                'project_types' => ['sme', 'agriculture'],
                'risk_levels' => ['low', 'medium']
            ],
            'Silver' => [
                'max_contribution' => 15000,
                'voting_weight' => 1.2,
                'project_types' => ['sme', 'agriculture', 'digital'],
                'risk_levels' => ['low', 'medium']
            ],
            'Gold' => [
                'max_contribution' => 50000,
                'voting_weight' => 1.5,
                'project_types' => ['real_estate', 'sme', 'agriculture', 'digital'],
                'risk_levels' => ['low', 'medium', 'high']
            ],
            'Diamond' => [
                'max_contribution' => 150000,
                'voting_weight' => 2.0,
                'project_types' => ['real_estate', 'infrastructure', 'sme', 'digital'],
                'risk_levels' => ['low', 'medium', 'high']
            ],
            'Elite' => [
                'max_contribution' => null, // No limit
                'voting_weight' => 3.0,
                'project_types' => ['real_estate', 'infrastructure', 'sme', 'digital', 'agriculture'],
                'risk_levels' => ['low', 'medium', 'high']
            ]
        ];
    }
}