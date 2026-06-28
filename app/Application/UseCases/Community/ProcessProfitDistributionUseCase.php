<?php

namespace App\Application\UseCases\Community;

use App\Domain\Community\Repositories\ProjectRepository;
use App\Domain\Community\Repositories\ProfitDistributionRepository;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectProfitDistribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessProfitDistributionUseCase
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ProfitDistributionRepository $profitDistributionRepository
    ) {}

    public function execute(int $projectId, float $profitAmount, string $distributionPeriod): array
    {
        return DB::transaction(function () use ($projectId, $profitAmount, $distributionPeriod) {
            $project = CommunityProject::with(['contributions.user.investmentTier'])->findOrFail($projectId);

            // Validate project eligibility for profit distribution
            $eligibilityCheck = $this->checkDistributionEligibility($project, $profitAmount);
            if (!$eligibilityCheck['eligible']) {
                return [
                    'success' => false,
                    'error' => $eligibilityCheck['reason']
                ];
            }

            // Calculate profit distributions
            $distributions = $this->calculateProfitDistributions($project, $profitAmount);

            // Create distribution records
            $distributionRecords = $this->createDistributionRecords(
                $project, 
                $distributions, 
                $distributionPeriod
            );

            // Update project profit tracking
            $this->updateProjectProfitTracking($project, $profitAmount);

            Log::info("Profit distribution processed", [
                'project_id' => $projectId,
                'profit_amount' => $profitAmount,
                'distribution_period' => $distributionPeriod,
                'total_distributions' => count($distributionRecords),
                'total_distributed' => array_sum(array_column($distributions, 'distribution_amount'))
            ]);

            return [
                'success' => true,
                'project_id' => $projectId,
                'project_name' => $project->name,
                'profit_amount' => $profitAmount,
                'distribution_period' => $distributionPeriod,
                'total_contributors' => count($distributions),
                'total_distributed' => array_sum(array_column($distributions, 'distribution_amount')),
                'distributions' => $distributions
            ];
        });
    }

    private function checkDistributionEligibility(CommunityProject $project, float $profitAmount): array
    {
        // Check project status
        if ($project->status !== 'ACTIVE') {
            return [
                'eligible' => false,
                'reason' => 'Only active projects can distribute profits'
            ];
        }

        // Check if project has contributions
        if ($project->contributions->isEmpty()) {
            return [
                'eligible' => false,
                'reason' => 'Project has no contributions to distribute profits to'
            ];
        }

        // Check minimum profit amount
        if ($profitAmount <= 0) {
            return [
                'eligible' => false,
                'reason' => 'Profit amount must be greater than zero'
            ];
        }

        return ['eligible' => true];
    }

    private function calculateProfitDistributions(CommunityProject $project, float $profitAmount): array
    {
        $distributions = [];
        $totalContributions = $project->contributions->sum('amount');

        // Group contributions by user to handle multiple contributions
        $userContributions = $project->contributions->groupBy('user_id');

        foreach ($userContributions as $userId => $contributions) {
            $user = $contributions->first()->user;
            $userTotalContribution = $contributions->sum('amount');
            
            // Calculate base profit share based on contribution percentage
            $contributionPercentage = $userTotalContribution / $totalContributions;
            $baseProfitShare = $profitAmount * $contributionPercentage;

            // Apply tier-based multiplier
            $tierMultiplier = $this->getTierProfitMultiplier($user->investmentTier?->name ?? 'Gold Member');
            $finalProfitShare = $baseProfitShare * $tierMultiplier;

            $distributions[] = [
                'user_id' => $userId,
                'user_name' => $user->name,
                'tier' => $user->investmentTier?->name ?? 'Gold Member',
                'total_contribution' => $userTotalContribution,
                'contribution_percentage' => $contributionPercentage * 100,
                'base_profit_share' => $baseProfitShare,
                'tier_multiplier' => $tierMultiplier,
                'distribution_amount' => $finalProfitShare
            ];
        }

        return $distributions;
    }

    private function getTierProfitMultiplier(string $tierName): float
    {
        return match ($tierName) {
            'Gold Member' => 1.0,     // Base multiplier
            'Diamond Member' => 1.2,  // 20% bonus
            'Elite Member' => 1.5,    // 50% bonus
            default => 1.0
        };
    }

    private function createDistributionRecords(
        CommunityProject $project, 
        array $distributions, 
        string $distributionPeriod
    ): array {
        $records = [];

        foreach ($distributions as $distribution) {
            $record = ProjectProfitDistribution::create([
                'project_id' => $project->id,
                'user_id' => $distribution['user_id'],
                'tier_id' => User::find($distribution['user_id'])->investment_tier_id,
                'contribution_amount' => $distribution['total_contribution'],
                'profit_share_percentage' => $distribution['contribution_percentage'],
                'tier_multiplier' => $distribution['tier_multiplier'],
                'base_amount' => $distribution['base_profit_share'],
                'bonus_amount' => $distribution['distribution_amount'] - $distribution['base_profit_share'],
                'total_amount' => $distribution['distribution_amount'],
                'distribution_period' => $distributionPeriod,
                'distributed_at' => now(),
                'status' => 'pending'
            ]);

            $records[] = $record;
        }

        return $records;
    }

    private function updateProjectProfitTracking(CommunityProject $project, float $profitAmount): void
    {
        $project->increment('total_profit_distributed', $profitAmount);
        $project->update(['last_profit_distribution' => now()]);
    }

    public function processDistributionPayments(int $projectId, ?string $distributionPeriod = null): array
    {
        $query = ProjectProfitDistribution::with(['user', 'project'])
            ->where('project_id', $projectId)
            ->where('status', 'pending');

        if ($distributionPeriod) {
            $query->where('distribution_period', $distributionPeriod);
        }

        $distributions = $query->get();
        $results = [
            'processed' => 0,
            'paid' => 0,
            'failed' => 0,
            'total_amount' => 0,
            'payments' => []
        ];

        foreach ($distributions as $distribution) {
            try {
                // In a real implementation, this would integrate with payment gateway
                $distribution->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);

                $results['processed']++;
                $results['paid']++;
                $results['total_amount'] += $distribution->total_amount;
                $results['payments'][] = [
                    'user_id' => $distribution->user_id,
                    'user_name' => $distribution->user->name,
                    'amount' => $distribution->total_amount,
                    'distribution_period' => $distribution->distribution_period
                ];

                Log::info("Profit distribution payment processed", [
                    'distribution_id' => $distribution->id,
                    'user_id' => $distribution->user_id,
                    'amount' => $distribution->total_amount
                ]);
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Profit distribution payment failed", [
                    'distribution_id' => $distribution->id,
                    'user_id' => $distribution->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    public function getUserProfitHistory(int $userId): array
    {
        $distributions = ProjectProfitDistribution::with(['project'])
            ->where('user_id', $userId)
            ->orderBy('distributed_at', 'desc')
            ->get();

        $totalEarned = $distributions->where('status', 'paid')->sum('total_amount');
        $pendingAmount = $distributions->where('status', 'pending')->sum('total_amount');
        $projectsCount = $distributions->groupBy('project_id')->count();

        $monthlyEarnings = $distributions
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->sum('total_amount');

        return [
            'user_id' => $userId,
            'total_earned' => $totalEarned,
            'pending_amount' => $pendingAmount,
            'projects_count' => $projectsCount,
            'monthly_earnings' => $monthlyEarnings,
            'distributions' => $distributions->map(function ($distribution) {
                return [
                    'project_id' => $distribution->project_id,
                    'project_name' => $distribution->project->name,
                    'contribution_amount' => $distribution->contribution_amount,
                    'profit_share_percentage' => $distribution->profit_share_percentage,
                    'tier_multiplier' => $distribution->tier_multiplier,
                    'base_amount' => $distribution->base_amount,
                    'bonus_amount' => $distribution->bonus_amount,
                    'total_amount' => $distribution->total_amount,
                    'distribution_period' => $distribution->distribution_period,
                    'status' => $distribution->status,
                    'distributed_at' => $distribution->distributed_at,
                    'paid_at' => $distribution->paid_at
                ];
            })
        ];
    }

    public function getProjectProfitSummary(int $projectId): array
    {
        $project = CommunityProject::with(['contributions', 'profitDistributions'])->findOrFail($projectId);
        
        $totalContributions = $project->contributions->sum('amount');
        $totalProfitDistributed = $project->profitDistributions->sum('total_amount');
        $totalProfitPaid = $project->profitDistributions->where('status', 'paid')->sum('total_amount');
        $pendingDistributions = $project->profitDistributions->where('status', 'pending')->sum('total_amount');

        $distributionsByPeriod = $project->profitDistributions
            ->groupBy('distribution_period')
            ->map(function ($distributions, $period) {
                return [
                    'period' => $period,
                    'total_amount' => $distributions->sum('total_amount'),
                    'contributors' => $distributions->count(),
                    'paid_amount' => $distributions->where('status', 'paid')->sum('total_amount'),
                    'pending_amount' => $distributions->where('status', 'pending')->sum('total_amount')
                ];
            });

        return [
            'project_id' => $projectId,
            'project_name' => $project->name,
            'project_status' => $project->status,
            'total_contributions' => $totalContributions,
            'total_profit_distributed' => $totalProfitDistributed,
            'total_profit_paid' => $totalProfitPaid,
            'pending_distributions' => $pendingDistributions,
            'roi_percentage' => $totalContributions > 0 ? ($totalProfitDistributed / $totalContributions) * 100 : 0,
            'last_distribution' => $project->last_profit_distribution,
            'distributions_by_period' => $distributionsByPeriod->values()
        ];
    }
}