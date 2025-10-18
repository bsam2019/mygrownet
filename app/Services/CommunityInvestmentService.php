<?php

namespace App\Services;

use App\Models\InvestmentOpportunity;
use App\Models\Investment;
use App\Models\User;
use App\Models\CommunityInvestmentDistribution;
use App\Models\ProfitDistribution;
use App\Models\InvestmentOpportunityVote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommunityInvestmentService
{
    /**
     * Create a community investment for a user
     */
    public function createCommunityInvestment(
        User $user,
        InvestmentOpportunity $opportunity,
        float $amount,
        array $additionalData = []
    ): Investment {
        if (!$opportunity->canUserContribute($user, $amount)) {
            throw new \Exception('User cannot contribute to this investment opportunity.');
        }

        return DB::transaction(function () use ($user, $opportunity, $amount, $additionalData) {
            $investment = Investment::create(array_merge([
                'user_id' => $user->id,
                'opportunity_id' => $opportunity->id,
                'community_project_id' => $opportunity->community_project_id,
                'amount' => $amount,
                'is_community_investment' => true,
                'investment_source' => 'community_project',
                'community_contribution_amount' => $amount,
                'tier_at_community_investment' => $user->currentTier?->name ?? 'Bronze',
                'voting_power_weight' => $opportunity->getUserVotingPower($user),
                'eligible_for_community_profits' => true,
                'community_investment_date' => now(),
                'status' => 'active',
                'investment_date' => now()
            ], $additionalData));

            // Update opportunity statistics
            $opportunity->increment('community_funding_raised', $amount);
            $opportunity->increment('community_contributors_count');

            // Log the investment
            Log::info('Community investment created', [
                'user_id' => $user->id,
                'opportunity_id' => $opportunity->id,
                'amount' => $amount,
                'investment_id' => $investment->id
            ]);

            return $investment;
        });
    }

    /**
     * Process a vote on an investment opportunity
     */
    public function processVote(
        InvestmentOpportunity $opportunity,
        User $user,
        string $voteType,
        ?string $reason = null,
        ?string $comments = null,
        ?array $criteriaScores = null
    ): InvestmentOpportunityVote {
        return DB::transaction(function () use ($opportunity, $user, $voteType, $reason, $comments, $criteriaScores) {
            $vote = InvestmentOpportunityVote::castVote(
                $opportunity,
                $user,
                $voteType,
                $reason,
                $comments,
                $criteriaScores
            );

            // Update community approval rating
            $this->updateCommunityApprovalRating($opportunity);

            Log::info('Investment opportunity vote cast', [
                'opportunity_id' => $opportunity->id,
                'user_id' => $user->id,
                'vote_type' => $voteType,
                'voting_power' => $vote->voting_power
            ]);

            return $vote;
        });
    }

    /**
     * Process voting results for an opportunity
     */
    public function processVotingResults(InvestmentOpportunity $opportunity): array
    {
        $results = $opportunity->processVotingResults();

        Log::info('Voting results processed', [
            'opportunity_id' => $opportunity->id,
            'status' => $results['status'],
            'approved' => $results['approved']
        ]);

        return $results;
    }

    /**
     * Create community profit distributions
     */
    public function createCommunityProfitDistributions(
        ProfitDistribution $profitDistribution,
        string $distributionType = 'quarterly'
    ): array {
        $communityOpportunities = InvestmentOpportunity::communityProjects()
            ->where('status', 'active')
            ->where('includes_quarterly_distributions', true)
            ->get();

        $distributions = [];

        foreach ($communityOpportunities as $opportunity) {
            if ($opportunity->community_profit_share_percentage <= 0) {
                continue;
            }

            try {
                $distribution = CommunityInvestmentDistribution::createDistribution(
                    $opportunity,
                    $profitDistribution,
                    $distributionType,
                    $this->generatePeriodLabel($profitDistribution, $distributionType)
                );

                $distributions[] = $distribution;

                Log::info('Community investment distribution created', [
                    'opportunity_id' => $opportunity->id,
                    'distribution_id' => $distribution->id,
                    'allocation_amount' => $distribution->community_allocation_amount
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create community investment distribution', [
                    'opportunity_id' => $opportunity->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $distributions;
    }

    /**
     * Process community profit distribution payments
     */
    public function processCommunityDistributionPayments(
        CommunityInvestmentDistribution $distribution,
        User $processedBy
    ): array {
        if (!$distribution->canBeDistributed()) {
            throw new \Exception('Distribution is not ready for payment processing.');
        }

        return DB::transaction(function () use ($distribution, $processedBy) {
            $results = $distribution->distribute($processedBy);

            Log::info('Community distribution payments processed', [
                'distribution_id' => $distribution->id,
                'processed_shares' => $results['processed_shares'],
                'total_distributed' => $results['total_amount_distributed'],
                'failed_payments' => $results['failed_payments']
            ]);

            return $results;
        });
    }

    /**
     * Get community investment statistics
     */
    public function getCommunityInvestmentStatistics(?string $period = null): array
    {
        $query = Investment::communityInvestments()->active();

        if ($period) {
            $query = $this->applyPeriodFilter($query, $period);
        }

        $investments = $query->get();

        return [
            'total_community_investments' => $investments->count(),
            'total_community_funding' => $investments->sum('community_contribution_amount'),
            'average_investment_amount' => $investments->avg('community_contribution_amount'),
            'unique_investors' => $investments->unique('user_id')->count(),
            'voting_participation_rate' => $investments->count() > 0 
                ? ($investments->where('participated_in_voting', true)->count() / $investments->count()) * 100 
                : 0,
            'tier_distribution' => $investments->groupBy('tier_at_community_investment')
                ->map(function ($tierInvestments) {
                    return [
                        'count' => $tierInvestments->count(),
                        'total_amount' => $tierInvestments->sum('community_contribution_amount'),
                        'average_amount' => $tierInvestments->avg('community_contribution_amount')
                    ];
                })->toArray(),
            'total_profits_distributed' => $this->getTotalCommunityProfitsDistributed($period),
            'active_opportunities' => InvestmentOpportunity::communityProjects()
                ->where('status', 'active')
                ->count()
        ];
    }

    /**
     * Get user's community investment summary
     */
    public function getUserCommunityInvestmentSummary(User $user): array
    {
        $communityInvestments = $user->investments()
            ->communityInvestments()
            ->with(['communityProject', 'investmentOpportunity', 'communityInvestmentProfitShares'])
            ->get();

        $totalInvested = $communityInvestments->sum('community_contribution_amount');
        $totalProfitsReceived = $communityInvestments->sum(function ($investment) {
            return $investment->getTotalCommunityProfitsReceived();
        });
        $pendingProfits = $communityInvestments->sum(function ($investment) {
            return $investment->getPendingCommunityProfits();
        });

        return [
            'total_community_investments' => $communityInvestments->count(),
            'total_invested' => $totalInvested,
            'total_profits_received' => $totalProfitsReceived,
            'pending_profits' => $pendingProfits,
            'overall_community_roi' => $totalInvested > 0 ? ($totalProfitsReceived / $totalInvested) * 100 : 0,
            'voting_participation_count' => $communityInvestments->where('participated_in_voting', true)->count(),
            'voting_participation_rate' => $communityInvestments->count() > 0 
                ? ($communityInvestments->where('participated_in_voting', true)->count() / $communityInvestments->count()) * 100 
                : 0,
            'investments_by_tier' => $communityInvestments->groupBy('tier_at_community_investment')
                ->map(function ($tierInvestments) {
                    return [
                        'count' => $tierInvestments->count(),
                        'total_amount' => $tierInvestments->sum('community_contribution_amount')
                    ];
                })->toArray(),
            'recent_investments' => $communityInvestments->sortByDesc('community_investment_date')
                ->take(5)
                ->map(function ($investment) {
                    return $investment->getCommunityInvestmentSummary();
                })->values()->toArray()
        ];
    }

    /**
     * Update community approval rating for an opportunity
     */
    private function updateCommunityApprovalRating(InvestmentOpportunity $opportunity): void
    {
        $votingResults = $opportunity->getVotingResults();
        $opportunity->update([
            'community_approval_rating' => $votingResults['approval_percentage']
        ]);
    }

    /**
     * Generate period label for distributions
     */
    private function generatePeriodLabel(ProfitDistribution $profitDistribution, string $distributionType): string
    {
        return match ($distributionType) {
            'monthly' => $profitDistribution->period_start->format('F Y'),
            'quarterly' => 'Q' . $profitDistribution->period_start->quarter . ' ' . $profitDistribution->period_start->year,
            'annual' => $profitDistribution->period_start->year,
            default => $profitDistribution->period_start->format('Y-m-d')
        };
    }

    /**
     * Apply period filter to query
     */
    private function applyPeriodFilter($query, string $period)
    {
        return match ($period) {
            'today' => $query->whereDate('community_investment_date', today()),
            'week' => $query->whereBetween('community_investment_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereBetween('community_investment_date', [now()->startOfMonth(), now()->endOfMonth()]),
            'quarter' => $query->whereBetween('community_investment_date', [now()->startOfQuarter(), now()->endOfQuarter()]),
            'year' => $query->whereBetween('community_investment_date', [now()->startOfYear(), now()->endOfYear()]),
            default => $query
        };
    }

    /**
     * Get total community profits distributed for a period
     */
    private function getTotalCommunityProfitsDistributed(?string $period = null): float
    {
        $query = CommunityInvestmentDistribution::distributed();

        if ($period) {
            $query = match ($period) {
                'month' => $query->whereBetween('distributed_at', [now()->startOfMonth(), now()->endOfMonth()]),
                'quarter' => $query->whereBetween('distributed_at', [now()->startOfQuarter(), now()->endOfQuarter()]),
                'year' => $query->whereBetween('distributed_at', [now()->startOfYear(), now()->endOfYear()]),
                default => $query
            };
        }

        return $query->sum('total_distributed_amount');
    }

    /**
     * Get community investment opportunities with voting status
     */
    public function getCommunityOpportunitiesWithVotingStatus(User $user): array
    {
        $opportunities = InvestmentOpportunity::communityProjects()
            ->where('status', 'active')
            ->with(['votes', 'communityProject'])
            ->get();

        return $opportunities->map(function ($opportunity) use ($user) {
            $canVote = $opportunity->canUserVote($user);
            $hasVoted = $opportunity->votes()->where('user_id', $user->id)->exists();
            $votingResults = $opportunity->getVotingResults();

            return [
                'opportunity' => $opportunity->toArray(),
                'voting_status' => [
                    'can_vote' => $canVote,
                    'has_voted' => $hasVoted,
                    'is_voting_active' => $opportunity->isVotingActive(),
                    'voting_results' => $votingResults,
                    'user_voting_power' => $canVote ? $opportunity->getUserVotingPower($user) : 0
                ],
                'statistics' => $opportunity->getOpportunityStatistics()
            ];
        })->toArray();
    }
}