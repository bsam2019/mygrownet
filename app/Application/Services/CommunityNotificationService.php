<?php

namespace App\Application\Services;

use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectProfitDistribution;
use App\Models\ProjectVote;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CommunityNotificationService
{
    /**
     * Send project contribution notifications
     */
    public function sendContributionNotifications(int $projectId, int $contributorId, float $amount): void
    {
        $project = CommunityProject::findOrFail($projectId);
        $contributor = User::findOrFail($contributorId);

        // Notify contributor
        $this->sendContributionConfirmation($contributor, $project, $amount);

        // Notify other contributors about project progress
        $this->notifyProjectProgress($project, $amount);

        // Check for milestone notifications
        $this->checkMilestoneNotifications($project);
    }

    /**
     * Send profit distribution notifications
     */
    public function sendProfitDistributionNotifications(int $projectId, string $distributionPeriod): void
    {
        $project = CommunityProject::findOrFail($projectId);
        $distributions = ProjectProfitDistribution::where('project_id', $projectId)
            ->where('distribution_period', $distributionPeriod)
            ->with('user')
            ->get();

        foreach ($distributions as $distribution) {
            $this->sendProfitDistributionNotification($distribution);
        }

        // Send summary to project stakeholders
        $this->sendDistributionSummaryNotification($project, $distributions);
    }

    /**
     * Send project status change notifications
     */
    public function sendProjectStatusNotifications(int $projectId, string $oldStatus, string $newStatus): void
    {
        $project = CommunityProject::with(['contributions.user'])->findOrFail($projectId);
        
        // Get all project stakeholders (contributors and voters)
        $stakeholders = $this->getProjectStakeholders($project);

        foreach ($stakeholders as $user) {
            $this->sendProjectStatusNotification($user, $project, $oldStatus, $newStatus);
        }
    }

    /**
     * Send voting notifications
     */
    public function sendVotingNotifications(int $projectId): void
    {
        $project = CommunityProject::findOrFail($projectId);
        
        // Get eligible voters (Gold+ tier members)
        $eligibleVoters = User::whereHas('investmentTier', function ($query) {
            $query->whereIn('name', ['Gold Member', 'Diamond Member', 'Elite Member']);
        })->get();

        foreach ($eligibleVoters as $voter) {
            // Check if they haven't voted yet
            $hasVoted = ProjectVote::where('project_id', $projectId)
                ->where('user_id', $voter->id)
                ->exists();

            if (!$hasVoted) {
                $this->sendVotingReminderNotification($voter, $project);
            }
        }
    }

    /**
     * Send project update notifications
     */
    public function sendProjectUpdateNotifications(int $projectId, string $updateTitle, string $updateDescription): void
    {
        $project = CommunityProject::with(['contributions.user'])->findOrFail($projectId);
        $stakeholders = $this->getProjectStakeholders($project);

        foreach ($stakeholders as $user) {
            $this->sendProjectUpdateNotification($user, $project, $updateTitle, $updateDescription);
        }
    }

    /**
     * Send monthly project summary notifications
     */
    public function sendMonthlyProjectSummaries(): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'projects_processed' => 0
        ];

        $activeProjects = CommunityProject::whereIn('status', ['FUNDING', 'ACTIVE'])->get();

        foreach ($activeProjects as $project) {
            try {
                $stakeholders = $this->getProjectStakeholders($project);
                $summary = $this->generateProjectMonthlySummary($project);

                foreach ($stakeholders as $user) {
                    $this->sendMonthlySummaryNotification($user, $project, $summary);
                    $results['sent']++;
                }

                $results['projects_processed']++;
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Monthly project summary failed", [
                    'project_id' => $project->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send contribution confirmation to contributor
     */
    private function sendContributionConfirmation(User $contributor, CommunityProject $project, float $amount): void
    {
        Log::info("Contribution confirmation notification", [
            'user_id' => $contributor->id,
            'project_id' => $project->id,
            'amount' => $amount,
            'message' => "Thank you for contributing K{$amount} to {$project->name}"
        ]);

        // In a real implementation, send actual notification
        // Notification::send($contributor, new ProjectContributionConfirmation($project, $amount));
    }

    /**
     * Notify other contributors about project progress
     */
    private function notifyProjectProgress(CommunityProject $project, float $newContribution): void
    {
        $contributors = User::whereHas('projectContributions', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->get();

        $fundingPercentage = ($project->current_amount / $project->target_amount) * 100;

        foreach ($contributors as $contributor) {
            Log::info("Project progress notification", [
                'user_id' => $contributor->id,
                'project_id' => $project->id,
                'funding_percentage' => $fundingPercentage,
                'message' => "{$project->name} is now {$fundingPercentage}% funded"
            ]);

            // Notification::send($contributor, new ProjectProgressNotification($project, $fundingPercentage));
        }
    }

    /**
     * Check and send milestone notifications
     */
    private function checkMilestoneNotifications(CommunityProject $project): void
    {
        $fundingPercentage = ($project->current_amount / $project->target_amount) * 100;
        $milestones = [25, 50, 75, 100];

        foreach ($milestones as $milestone) {
            if ($fundingPercentage >= $milestone && !$this->milestoneNotificationSent($project, $milestone)) {
                $this->sendMilestoneNotification($project, $milestone);
                $this->recordMilestoneNotification($project, $milestone);
            }
        }
    }

    /**
     * Send milestone notification
     */
    private function sendMilestoneNotification(CommunityProject $project, int $milestone): void
    {
        $stakeholders = $this->getProjectStakeholders($project);

        foreach ($stakeholders as $user) {
            Log::info("Milestone notification", [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'milestone' => $milestone,
                'message' => "{$project->name} has reached {$milestone}% funding milestone"
            ]);

            // Notification::send($user, new ProjectMilestoneNotification($project, $milestone));
        }
    }

    /**
     * Send profit distribution notification
     */
    private function sendProfitDistributionNotification(ProjectProfitDistribution $distribution): void
    {
        Log::info("Profit distribution notification", [
            'user_id' => $distribution->user_id,
            'project_id' => $distribution->project_id,
            'amount' => $distribution->total_amount,
            'period' => $distribution->distribution_period,
            'message' => "You received K{$distribution->total_amount} profit distribution from {$distribution->project->name}"
        ]);

        // Notification::send($distribution->user, new ProfitDistributionNotification($distribution));
    }

    /**
     * Send distribution summary notification
     */
    private function sendDistributionSummaryNotification(CommunityProject $project, $distributions): void
    {
        $totalDistributed = $distributions->sum('total_amount');
        $contributorCount = $distributions->count();

        Log::info("Distribution summary notification", [
            'project_id' => $project->id,
            'total_distributed' => $totalDistributed,
            'contributor_count' => $contributorCount,
            'message' => "Distributed K{$totalDistributed} to {$contributorCount} contributors for {$project->name}"
        ]);
    }

    /**
     * Send project status notification
     */
    private function sendProjectStatusNotification(User $user, CommunityProject $project, string $oldStatus, string $newStatus): void
    {
        $statusMessages = [
            'FUNDING' => 'is now accepting contributions',
            'ACTIVE' => 'is fully funded and now active',
            'COMPLETED' => 'has been completed successfully',
            'CANCELLED' => 'has been cancelled',
            'REJECTED' => 'was rejected by community vote'
        ];

        $message = $statusMessages[$newStatus] ?? "status changed to {$newStatus}";

        Log::info("Project status notification", [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'message' => "{$project->name} {$message}"
        ]);

        // Notification::send($user, new ProjectStatusNotification($project, $oldStatus, $newStatus));
    }

    /**
     * Send voting reminder notification
     */
    private function sendVotingReminderNotification(User $voter, CommunityProject $project): void
    {
        Log::info("Voting reminder notification", [
            'user_id' => $voter->id,
            'project_id' => $project->id,
            'message' => "Please vote on community project: {$project->name}"
        ]);

        // Notification::send($voter, new VotingReminderNotification($project));
    }

    /**
     * Send project update notification
     */
    private function sendProjectUpdateNotification(User $user, CommunityProject $project, string $title, string $description): void
    {
        Log::info("Project update notification", [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'update_title' => $title,
            'message' => "New update for {$project->name}: {$title}"
        ]);

        // Notification::send($user, new ProjectUpdateNotification($project, $title, $description));
    }

    /**
     * Send monthly summary notification
     */
    private function sendMonthlySummaryNotification(User $user, CommunityProject $project, array $summary): void
    {
        Log::info("Monthly project summary notification", [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'summary' => $summary,
            'message' => "Monthly summary for {$project->name}"
        ]);

        // Notification::send($user, new MonthlyProjectSummaryNotification($project, $summary));
    }

    /**
     * Get project stakeholders (contributors and voters)
     */
    private function getProjectStakeholders(CommunityProject $project): \Illuminate\Database\Eloquent\Collection
    {
        $contributorIds = $project->contributions->pluck('user_id')->unique();
        $voterIds = ProjectVote::where('project_id', $project->id)->pluck('user_id')->unique();
        
        $stakeholderIds = $contributorIds->merge($voterIds)->unique();
        
        return User::whereIn('id', $stakeholderIds)->get();
    }

    /**
     * Generate monthly project summary
     */
    private function generateProjectMonthlySummary(CommunityProject $project): array
    {
        $monthlyContributions = ProjectContribution::where('project_id', $project->id)
            ->whereMonth('contributed_at', now()->month)
            ->whereYear('contributed_at', now()->year)
            ->sum('amount');

        $monthlyDistributions = ProjectProfitDistribution::where('project_id', $project->id)
            ->whereMonth('distributed_at', now()->month)
            ->whereYear('distributed_at', now()->year)
            ->sum('total_amount');

        return [
            'project_name' => $project->name,
            'status' => $project->status,
            'funding_percentage' => ($project->current_amount / $project->target_amount) * 100,
            'monthly_contributions' => $monthlyContributions,
            'monthly_distributions' => $monthlyDistributions,
            'total_contributors' => $project->contributor_count,
            'last_update' => $project->updates()->latest()->first()?->created_at
        ];
    }

    /**
     * Check if milestone notification was already sent
     */
    private function milestoneNotificationSent(CommunityProject $project, int $milestone): bool
    {
        // In a real implementation, you would track this in a separate table
        // For now, we'll use a simple cache or project metadata
        return false; // Simplified for this example
    }

    /**
     * Record that milestone notification was sent
     */
    private function recordMilestoneNotification(CommunityProject $project, int $milestone): void
    {
        // In a real implementation, record this in a notifications tracking table
        Log::info("Milestone notification recorded", [
            'project_id' => $project->id,
            'milestone' => $milestone
        ]);
    }

    /**
     * Get notification preferences for user
     */
    public function getUserNotificationPreferences(int $userId): array
    {
        // In a real implementation, this would come from user preferences table
        return [
            'project_contributions' => true,
            'profit_distributions' => true,
            'project_status_changes' => true,
            'voting_reminders' => true,
            'project_updates' => true,
            'monthly_summaries' => true,
            'milestone_notifications' => true
        ];
    }

    /**
     * Update user notification preferences
     */
    public function updateUserNotificationPreferences(int $userId, array $preferences): array
    {
        // In a real implementation, this would update user preferences table
        Log::info("Notification preferences updated", [
            'user_id' => $userId,
            'preferences' => $preferences
        ]);

        return [
            'success' => true,
            'preferences' => $preferences
        ];
    }
}