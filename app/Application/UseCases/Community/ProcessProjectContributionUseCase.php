<?php

namespace App\Application\UseCases\Community;

use App\Domain\Community\Repositories\ProjectRepository;
use App\Domain\Community\Repositories\ContributionRepository;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Models\User;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessProjectContributionUseCase
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ContributionRepository $contributionRepository
    ) {}

    public function execute(int $userId, int $projectId, float $contributionAmount): array
    {
        return DB::transaction(function () use ($userId, $projectId, $contributionAmount) {
            $user = User::with('investmentTier')->findOrFail($userId);
            $project = CommunityProject::findOrFail($projectId);

            // Validate contribution eligibility
            $eligibilityCheck = $this->checkContributionEligibility($user, $project, $contributionAmount);
            if (!$eligibilityCheck['eligible']) {
                return [
                    'success' => false,
                    'error' => $eligibilityCheck['reason']
                ];
            }

            // Create contribution
            $contribution = $this->createContribution($user, $project, $contributionAmount);

            // Update project funding
            $this->updateProjectFunding($project, $contributionAmount);

            // Check if project is fully funded
            $projectStatus = $this->checkProjectFundingStatus($project);

            Log::info("Project contribution processed", [
                'user_id' => $userId,
                'project_id' => $projectId,
                'contribution_amount' => $contributionAmount,
                'project_status' => $projectStatus['status']
            ]);

            return [
                'success' => true,
                'contribution_id' => $contribution->id,
                'contribution_amount' => $contributionAmount,
                'project_current_amount' => $project->current_amount,
                'project_target_amount' => $project->target_amount,
                'funding_percentage' => ($project->current_amount / $project->target_amount) * 100,
                'project_status' => $projectStatus
            ];
        });
    }

    private function checkContributionEligibility(User $user, CommunityProject $project, float $amount): array
    {
        // Check if user has required tier for community projects
        if (!$user->investmentTier) {
            return [
                'eligible' => false,
                'reason' => 'User must have an active membership tier to contribute to community projects'
            ];
        }

        // Check minimum tier requirement (Gold+ for community projects)
        $requiredTiers = ['Gold Member', 'Diamond Member', 'Elite Member'];
        if (!in_array($user->investmentTier->name, $requiredTiers)) {
            return [
                'eligible' => false,
                'reason' => 'Community project contributions require Gold tier or higher'
            ];
        }

        // Check project status
        if ($project->status !== 'FUNDING') {
            return [
                'eligible' => false,
                'reason' => 'Project is not currently accepting contributions'
            ];
        }

        // Check minimum contribution amount
        $minContribution = $this->getMinimumContribution($user->investmentTier->name);
        if ($amount < $minContribution) {
            return [
                'eligible' => false,
                'reason' => "Minimum contribution for {$user->investmentTier->name} is K{$minContribution}"
            ];
        }

        // Check if contribution would exceed project target
        if (($project->current_amount + $amount) > $project->target_amount) {
            $remainingAmount = $project->target_amount - $project->current_amount;
            return [
                'eligible' => false,
                'reason' => "Contribution exceeds project funding target. Maximum contribution: K{$remainingAmount}"
            ];
        }

        // Check user's monthly contribution limit
        $monthlyLimit = $this->getMonthlyContributionLimit($user->investmentTier->name);
        $currentMonthContributions = $this->getCurrentMonthContributions($user->id);
        
        if (($currentMonthContributions + $amount) > $monthlyLimit) {
            $remainingLimit = $monthlyLimit - $currentMonthContributions;
            return [
                'eligible' => false,
                'reason' => "Monthly contribution limit exceeded. Remaining limit: K{$remainingLimit}"
            ];
        }

        return ['eligible' => true];
    }

    private function getMinimumContribution(string $tierName): float
    {
        return match ($tierName) {
            'Gold Member' => 1000,
            'Diamond Member' => 2500,
            'Elite Member' => 5000,
            default => 1000
        };
    }

    private function getMonthlyContributionLimit(string $tierName): float
    {
        return match ($tierName) {
            'Gold Member' => 10000,
            'Diamond Member' => 25000,
            'Elite Member' => 50000,
            default => 10000
        };
    }

    private function getCurrentMonthContributions(int $userId): float
    {
        return ProjectContribution::where('user_id', $userId)
            ->whereMonth('contributed_at', now()->month)
            ->whereYear('contributed_at', now()->year)
            ->sum('amount');
    }

    private function createContribution(User $user, CommunityProject $project, float $amount): ProjectContribution
    {
        return ProjectContribution::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'tier_at_contribution' => $user->investmentTier->name,
            'contributed_at' => now(),
        ]);
    }

    private function updateProjectFunding(CommunityProject $project, float $amount): void
    {
        $project->increment('current_amount', $amount);
        $project->increment('contributor_count');
    }

    private function checkProjectFundingStatus(CommunityProject $project): array
    {
        $project->refresh();
        $fundingPercentage = ($project->current_amount / $project->target_amount) * 100;

        if ($fundingPercentage >= 100) {
            $project->update(['status' => 'ACTIVE']);
            return [
                'status' => 'ACTIVE',
                'message' => 'Project is fully funded and now active',
                'funding_percentage' => 100
            ];
        } elseif ($fundingPercentage >= 75) {
            return [
                'status' => 'FUNDING',
                'message' => 'Project is nearly fully funded',
                'funding_percentage' => $fundingPercentage
            ];
        } else {
            return [
                'status' => 'FUNDING',
                'message' => 'Project is still seeking funding',
                'funding_percentage' => $fundingPercentage
            ];
        }
    }

    public function getUserContributionSummary(int $userId): array
    {
        $user = User::findOrFail($userId);
        
        $contributions = ProjectContribution::with(['project'])
            ->where('user_id', $userId)
            ->get();

        $totalContributed = $contributions->sum('amount');
        $projectsContributed = $contributions->groupBy('project_id')->count();
        $activeProjects = $contributions->whereIn('project.status', ['FUNDING', 'ACTIVE'])->count();

        $monthlyContributions = ProjectContribution::where('user_id', $userId)
            ->whereMonth('contributed_at', now()->month)
            ->whereYear('contributed_at', now()->year)
            ->sum('amount');

        $monthlyLimit = $this->getMonthlyContributionLimit($user->investmentTier?->name ?? 'Gold Member');

        return [
            'user_id' => $userId,
            'tier' => $user->investmentTier?->name,
            'total_contributed' => $totalContributed,
            'projects_contributed' => $projectsContributed,
            'active_projects' => $activeProjects,
            'monthly_contributions' => $monthlyContributions,
            'monthly_limit' => $monthlyLimit,
            'remaining_monthly_limit' => max(0, $monthlyLimit - $monthlyContributions),
            'contributions' => $contributions->map(function ($contribution) {
                return [
                    'project_id' => $contribution->project_id,
                    'project_name' => $contribution->project->name,
                    'amount' => $contribution->amount,
                    'contributed_at' => $contribution->contributed_at,
                    'project_status' => $contribution->project->status
                ];
            })
        ];
    }
}