<?php

namespace App\Application\Services;

use App\Application\UseCases\Community\ProcessProjectContributionUseCase;
use App\Application\UseCases\Community\ProcessProfitDistributionUseCase;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectVote;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectManagementService
{
    public function __construct(
        private ProcessProjectContributionUseCase $processProjectContributionUseCase,
        private ProcessProfitDistributionUseCase $processProfitDistributionUseCase
    ) {}

    /**
     * Create a new community project
     */
    public function createProject(array $projectData): array
    {
        try {
            $project = DB::transaction(function () use ($projectData) {
                return CommunityProject::create([
                    'name' => $projectData['name'],
                    'description' => $projectData['description'],
                    'type' => $projectData['type'] ?? 'SME',
                    'target_amount' => $projectData['target_amount'],
                    'current_amount' => 0,
                    'contributor_count' => 0,
                    'status' => 'PLANNING',
                    'expected_roi' => $projectData['expected_roi'] ?? 15.0,
                    'project_duration_months' => $projectData['duration_months'] ?? 12,
                    'minimum_contribution' => $projectData['minimum_contribution'] ?? 1000,
                    'created_at' => now()
                ]);
            });

            Log::info("Community project created", [
                'project_id' => $project->id,
                'name' => $project->name,
                'target_amount' => $project->target_amount
            ]);

            return [
                'success' => true,
                'project' => $project
            ];
        } catch (\Exception $e) {
            Log::error("Project creation failed", [
                'error' => $e->getMessage(),
                'project_data' => $projectData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process project contribution
     */
    public function processContribution(int $userId, int $projectId, float $amount): array
    {
        return $this->processProjectContributionUseCase->execute($userId, $projectId, $amount);
    }

    /**
     * Process project voting
     */
    public function processProjectVote(int $userId, int $projectId, string $voteType, ?string $comments = null): array
    {
        try {
            $user = User::with('investmentTier')->findOrFail($userId);
            $project = CommunityProject::findOrFail($projectId);

            // Check voting eligibility
            $eligibilityCheck = $this->checkVotingEligibility($user, $project);
            if (!$eligibilityCheck['eligible']) {
                return [
                    'success' => false,
                    'error' => $eligibilityCheck['reason']
                ];
            }

            // Check if user already voted
            $existingVote = ProjectVote::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->first();

            if ($existingVote) {
                return [
                    'success' => false,
                    'error' => 'User has already voted on this project'
                ];
            }

            // Calculate voting power based on tier and contributions
            $votingPower = $this->calculateVotingPower($user, $project);

            $vote = ProjectVote::create([
                'project_id' => $projectId,
                'user_id' => $userId,
                'vote_type' => $voteType, // 'approve', 'reject', 'abstain'
                'voting_power' => $votingPower,
                'comments' => $comments,
                'voted_at' => now()
            ]);

            // Check if voting threshold is met
            $votingResult = $this->checkVotingThreshold($project);

            Log::info("Project vote processed", [
                'user_id' => $userId,
                'project_id' => $projectId,
                'vote_type' => $voteType,
                'voting_power' => $votingPower
            ]);

            return [
                'success' => true,
                'vote_id' => $vote->id,
                'voting_power' => $votingPower,
                'voting_result' => $votingResult
            ];
        } catch (\Exception $e) {
            Log::error("Project voting failed", [
                'user_id' => $userId,
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update project status through lifecycle
     */
    public function updateProjectStatus(int $projectId, string $newStatus, ?string $reason = null): array
    {
        try {
            $project = CommunityProject::findOrFail($projectId);
            $oldStatus = $project->status;

            // Validate status transition
            $transitionCheck = $this->validateStatusTransition($oldStatus, $newStatus);
            if (!$transitionCheck['valid']) {
                return [
                    'success' => false,
                    'error' => $transitionCheck['reason']
                ];
            }

            $project->update([
                'status' => $newStatus,
                'status_updated_at' => now()
            ]);

            // Create project update record
            $this->createProjectUpdate($project, "Status changed from {$oldStatus} to {$newStatus}", $reason);

            // Handle status-specific actions
            $this->handleStatusChange($project, $oldStatus, $newStatus);

            Log::info("Project status updated", [
                'project_id' => $projectId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'project' => $project
            ];
        } catch (\Exception $e) {
            Log::error("Project status update failed", [
                'project_id' => $projectId,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process profit distribution for a project
     */
    public function distributeProfits(int $projectId, float $profitAmount, string $distributionPeriod): array
    {
        return $this->processProfitDistributionUseCase->execute($projectId, $profitAmount, $distributionPeriod);
    }

    /**
     * Get project dashboard data
     */
    public function getProjectDashboard(int $projectId): array
    {
        $project = CommunityProject::with([
            'contributions.user',
            'votes.user',
            'updates',
            'profitDistributions'
        ])->findOrFail($projectId);

        $contributionSummary = $this->getContributionSummary($project);
        $votingSummary = $this->getVotingSummary($project);
        $profitSummary = $this->processProfitDistributionUseCase->getProjectProfitSummary($projectId);

        return [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'type' => $project->type,
                'status' => $project->status,
                'target_amount' => $project->target_amount,
                'current_amount' => $project->current_amount,
                'funding_percentage' => ($project->current_amount / $project->target_amount) * 100,
                'contributor_count' => $project->contributor_count,
                'expected_roi' => $project->expected_roi,
                'project_duration_months' => $project->project_duration_months,
                'created_at' => $project->created_at,
                'status_updated_at' => $project->status_updated_at
            ],
            'contribution_summary' => $contributionSummary,
            'voting_summary' => $votingSummary,
            'profit_summary' => $profitSummary,
            'recent_updates' => $project->updates()->latest()->take(5)->get(),
            'timeline' => $this->getProjectTimeline($project)
        ];
    }

    /**
     * Get all active projects
     */
    public function getActiveProjects(): array
    {
        $projects = CommunityProject::whereIn('status', ['FUNDING', 'ACTIVE'])
            ->with(['contributions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'type' => $project->type,
                'status' => $project->status,
                'target_amount' => $project->target_amount,
                'current_amount' => $project->current_amount,
                'funding_percentage' => ($project->current_amount / $project->target_amount) * 100,
                'contributor_count' => $project->contributor_count,
                'expected_roi' => $project->expected_roi,
                'minimum_contribution' => $project->minimum_contribution,
                'created_at' => $project->created_at
            ];
        })->toArray();
    }

    /**
     * Get user's project participation summary
     */
    public function getUserProjectSummary(int $userId): array
    {
        $contributionSummary = $this->processProjectContributionUseCase->getUserContributionSummary($userId);
        $profitHistory = $this->processProfitDistributionUseCase->getUserProfitHistory($userId);

        $votes = ProjectVote::with('project')->where('user_id', $userId)->get();
        $votingSummary = [
            'total_votes' => $votes->count(),
            'approve_votes' => $votes->where('vote_type', 'approve')->count(),
            'reject_votes' => $votes->where('vote_type', 'reject')->count(),
            'abstain_votes' => $votes->where('vote_type', 'abstain')->count(),
            'total_voting_power' => $votes->sum('voting_power')
        ];

        return [
            'user_id' => $userId,
            'contribution_summary' => $contributionSummary,
            'profit_history' => $profitHistory,
            'voting_summary' => $votingSummary,
            'active_projects' => $this->getUserActiveProjects($userId)
        ];
    }

    /**
     * Check voting eligibility
     */
    private function checkVotingEligibility(User $user, CommunityProject $project): array
    {
        // Check if user has required tier
        $requiredTiers = ['Gold Member', 'Diamond Member', 'Elite Member'];
        if (!$user->investmentTier || !in_array($user->investmentTier->name, $requiredTiers)) {
            return [
                'eligible' => false,
                'reason' => 'Voting requires Gold tier or higher'
            ];
        }

        // Check project status
        if (!in_array($project->status, ['PLANNING', 'FUNDING'])) {
            return [
                'eligible' => false,
                'reason' => 'Voting is only available during planning and funding phases'
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Calculate voting power based on tier and contributions
     */
    private function calculateVotingPower(User $user, CommunityProject $project): float
    {
        // Base voting power by tier
        $basePower = match ($user->investmentTier->name) {
            'Gold Member' => 1.0,
            'Diamond Member' => 2.0,
            'Elite Member' => 3.0,
            default => 1.0
        };

        // Additional power based on contributions to this project
        $userContributions = ProjectContribution::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->sum('amount');

        $contributionBonus = $userContributions > 0 ? min(2.0, $userContributions / 10000) : 0;

        return $basePower + $contributionBonus;
    }

    /**
     * Check if voting threshold is met
     */
    private function checkVotingThreshold(CommunityProject $project): array
    {
        $votes = ProjectVote::where('project_id', $project->id)->get();
        
        $totalVotingPower = $votes->sum('voting_power');
        $approveVotes = $votes->where('vote_type', 'approve')->sum('voting_power');
        $rejectVotes = $votes->where('vote_type', 'reject')->sum('voting_power');

        $approvalPercentage = $totalVotingPower > 0 ? ($approveVotes / $totalVotingPower) * 100 : 0;
        $rejectionPercentage = $totalVotingPower > 0 ? ($rejectVotes / $totalVotingPower) * 100 : 0;

        // Require 60% approval to move to funding
        if ($approvalPercentage >= 60 && $project->status === 'PLANNING') {
            $project->update(['status' => 'FUNDING']);
            return [
                'threshold_met' => true,
                'result' => 'approved',
                'approval_percentage' => $approvalPercentage
            ];
        }

        // Reject if 40% or more reject
        if ($rejectionPercentage >= 40) {
            $project->update(['status' => 'REJECTED']);
            return [
                'threshold_met' => true,
                'result' => 'rejected',
                'rejection_percentage' => $rejectionPercentage
            ];
        }

        return [
            'threshold_met' => false,
            'approval_percentage' => $approvalPercentage,
            'rejection_percentage' => $rejectionPercentage,
            'total_voting_power' => $totalVotingPower
        ];
    }

    /**
     * Validate status transition
     */
    private function validateStatusTransition(string $oldStatus, string $newStatus): array
    {
        $validTransitions = [
            'PLANNING' => ['FUNDING', 'REJECTED'],
            'FUNDING' => ['ACTIVE', 'CANCELLED'],
            'ACTIVE' => ['COMPLETED', 'SUSPENDED'],
            'SUSPENDED' => ['ACTIVE', 'CANCELLED'],
            'COMPLETED' => [], // Terminal state
            'CANCELLED' => [], // Terminal state
            'REJECTED' => [] // Terminal state
        ];

        if (!isset($validTransitions[$oldStatus]) || !in_array($newStatus, $validTransitions[$oldStatus])) {
            return [
                'valid' => false,
                'reason' => "Invalid status transition from {$oldStatus} to {$newStatus}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Handle status change actions
     */
    private function handleStatusChange(CommunityProject $project, string $oldStatus, string $newStatus): void
    {
        switch ($newStatus) {
            case 'FUNDING':
                $this->createProjectUpdate($project, "Project approved and now accepting contributions");
                break;
            case 'ACTIVE':
                $this->createProjectUpdate($project, "Project fully funded and now active");
                break;
            case 'COMPLETED':
                $this->createProjectUpdate($project, "Project completed successfully");
                break;
            case 'CANCELLED':
                $this->createProjectUpdate($project, "Project cancelled");
                // TODO: Process refunds for contributors
                break;
            case 'REJECTED':
                $this->createProjectUpdate($project, "Project rejected by community vote");
                break;
        }
    }

    /**
     * Create project update
     */
    private function createProjectUpdate(CommunityProject $project, string $title, ?string $description = null): void
    {
        ProjectUpdate::create([
            'project_id' => $project->id,
            'title' => $title,
            'description' => $description,
            'update_type' => 'STATUS_CHANGE',
            'created_at' => now()
        ]);
    }

    /**
     * Get contribution summary for a project
     */
    private function getContributionSummary(CommunityProject $project): array
    {
        $contributions = $project->contributions;
        
        return [
            'total_amount' => $contributions->sum('amount'),
            'contributor_count' => $contributions->groupBy('user_id')->count(),
            'average_contribution' => $contributions->avg('amount'),
            'largest_contribution' => $contributions->max('amount'),
            'contributions_by_tier' => $contributions->groupBy('tier_at_contribution')->map(function ($tierContributions) {
                return [
                    'count' => $tierContributions->count(),
                    'total_amount' => $tierContributions->sum('amount')
                ];
            })
        ];
    }

    /**
     * Get voting summary for a project
     */
    private function getVotingSummary(CommunityProject $project): array
    {
        $votes = $project->votes;
        
        return [
            'total_votes' => $votes->count(),
            'total_voting_power' => $votes->sum('voting_power'),
            'approve_votes' => $votes->where('vote_type', 'approve')->count(),
            'reject_votes' => $votes->where('vote_type', 'reject')->count(),
            'abstain_votes' => $votes->where('vote_type', 'abstain')->count(),
            'approval_percentage' => $votes->sum('voting_power') > 0 ? 
                ($votes->where('vote_type', 'approve')->sum('voting_power') / $votes->sum('voting_power')) * 100 : 0
        ];
    }

    /**
     * Get project timeline
     */
    private function getProjectTimeline(CommunityProject $project): array
    {
        $timeline = [];
        
        // Add creation
        $timeline[] = [
            'date' => $project->created_at,
            'event' => 'Project Created',
            'description' => 'Community project was created and entered planning phase'
        ];

        // Add status changes from updates
        foreach ($project->updates as $update) {
            if ($update->update_type === 'STATUS_CHANGE') {
                $timeline[] = [
                    'date' => $update->created_at,
                    'event' => $update->title,
                    'description' => $update->description
                ];
            }
        }

        // Add major milestones
        if ($project->current_amount >= $project->target_amount * 0.5) {
            $timeline[] = [
                'date' => $project->contributions()->orderBy('contributed_at')->skip(floor($project->contributions->count() / 2))->first()?->contributed_at,
                'event' => '50% Funding Milestone',
                'description' => 'Project reached 50% of funding target'
            ];
        }

        return collect($timeline)->sortBy('date')->values()->toArray();
    }

    /**
     * Get user's active projects
     */
    private function getUserActiveProjects(int $userId): array
    {
        $projectIds = ProjectContribution::where('user_id', $userId)
            ->distinct('project_id')
            ->pluck('project_id');

        return CommunityProject::whereIn('id', $projectIds)
            ->whereIn('status', ['FUNDING', 'ACTIVE'])
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'funding_percentage' => ($project->current_amount / $project->target_amount) * 100
                ];
            })
            ->toArray();
    }
}