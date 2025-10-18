<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Community\Repositories\ProjectRepository;
use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\ProjectStatus;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Domain\MLM\ValueObjects\UserId;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentProjectRepository implements ProjectRepository
{
    public function findById(ProjectId $id): ?array
    {
        $project = CommunityProject::with(['contributions', 'votes', 'profitDistributions', 'updates'])
            ->find($id->value());

        return $project ? $this->toProjectArray($project) : null;
    }

    public function findByStatus(ProjectStatus $status): array
    {
        $projects = CommunityProject::where('status', $status->value())
            ->with(['contributions', 'creator', 'projectManager'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function findActiveFundingProjects(): array
    {
        $projects = CommunityProject::funding()
            ->with(['contributions', 'creator'])
            ->orderBy('funding_end_date')
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function findProjectsForTier(string $tierName): array
    {
        $projects = CommunityProject::forTier($tierName)
            ->active()
            ->with(['contributions', 'creator'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function findFeaturedProjects(): array
    {
        $projects = CommunityProject::featured()
            ->active()
            ->with(['contributions', 'creator'])
            ->orderBy('community_approval_rating', 'desc')
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function findProjectsByType(string $type): array
    {
        $projects = CommunityProject::byType($type)
            ->active()
            ->with(['contributions', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function findProjectsByRiskLevel(string $riskLevel): array
    {
        $projects = CommunityProject::byRiskLevel($riskLevel)
            ->active()
            ->with(['contributions', 'creator'])
            ->orderBy('expected_annual_return', 'desc')
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function findUserProjects(UserId $userId): array
    {
        $projects = CommunityProject::whereHas('contributions', function ($query) use ($userId) {
            $query->where('user_id', $userId->value())
                  ->where('status', 'confirmed');
        })
        ->with(['contributions' => function ($query) use ($userId) {
            $query->where('user_id', $userId->value());
        }])
        ->orderBy('created_at', 'desc')
        ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function canUserContribute(UserId $userId, ProjectId $projectId): bool
    {
        $project = CommunityProject::find($projectId->value());
        $user = User::find($userId->value());

        if (!$project || !$user) {
            return false;
        }

        return $project->canUserContribute($user);
    }

    public function getProjectFundingStats(ProjectId $projectId): array
    {
        $project = CommunityProject::with(['contributions'])->find($projectId->value());

        if (!$project) {
            return [];
        }

        $confirmedContributions = $project->contributions()->confirmed()->get();
        
        return [
            'project_id' => $project->id,
            'target_amount' => $project->target_amount,
            'current_amount' => $project->current_amount,
            'remaining_amount' => $project->getRemainingFundingAmount(),
            'funding_progress' => $project->getFundingProgress(),
            'total_contributors' => $project->total_contributors,
            'average_contribution' => $confirmedContributions->count() > 0 
                ? $confirmedContributions->avg('amount') 
                : 0,
            'largest_contribution' => $confirmedContributions->max('amount') ?? 0,
            'smallest_contribution' => $confirmedContributions->min('amount') ?? 0,
            'days_remaining' => $project->getDaysRemainingForFunding(),
            'funding_velocity' => $this->calculateFundingVelocity($project),
            'projected_completion_date' => $this->calculateProjectedCompletionDate($project),
        ];
    }

    public function getUserProjectContribution(UserId $userId, ProjectId $projectId): array
    {
        $project = CommunityProject::find($projectId->value());
        $user = User::find($userId->value());

        if (!$project || !$user) {
            return [];
        }

        $contributions = $project->contributions()
            ->where('user_id', $userId->value())
            ->confirmed()
            ->get();

        $totalContribution = $contributions->sum('amount');
        $contributionPercentage = $project->current_amount > 0 
            ? ($totalContribution / $project->current_amount) * 100 
            : 0;

        return [
            'user_id' => $userId->value(),
            'project_id' => $projectId->value(),
            'total_contribution' => $totalContribution,
            'contribution_count' => $contributions->count(),
            'contribution_percentage' => $contributionPercentage,
            'voting_power' => $project->getUserVotingPower($user),
            'expected_returns' => $project->calculateExpectedReturns($user),
            'first_contribution_date' => $contributions->min('contributed_at'),
            'last_contribution_date' => $contributions->max('contributed_at'),
            'can_contribute_more' => $project->canUserContribute($user),
        ];
    }

    public function findProjectsRequiringAttention(): array
    {
        $projects = CommunityProject::where(function ($query) {
            $query->where('status', 'funding')
                  ->where('funding_end_date', '<=', now()->addDays(7))
                  ->where('current_amount', '<', DB::raw('target_amount'));
        })
        ->orWhere(function ($query) {
            $query->where('status', 'active')
                  ->where('expected_completion_date', '<=', now()->addDays(30));
        })
        ->with(['contributions', 'creator'])
        ->orderBy('funding_end_date')
        ->get();

        return $projects->map(function ($project) {
            $projectArray = $this->toProjectArray($project);
            $projectArray['attention_reason'] = $this->getAttentionReason($project);
            return $projectArray;
        })->toArray();
    }

    public function getProjectPerformanceMetrics(ProjectId $projectId): array
    {
        $project = CommunityProject::with(['contributions', 'profitDistributions'])
            ->find($projectId->value());

        if (!$project) {
            return [];
        }

        $contributions = $project->contributions()->confirmed()->get();
        $distributions = $project->profitDistributions()->paid()->get();

        $totalDistributed = $distributions->sum('distribution_amount');
        $totalContributed = $contributions->sum('amount');
        $actualROI = $totalContributed > 0 ? ($totalDistributed / $totalContributed) * 100 : 0;

        return [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'total_contributed' => $totalContributed,
            'total_distributed' => $totalDistributed,
            'actual_roi' => $actualROI,
            'expected_roi' => $project->expected_annual_return,
            'roi_performance' => $project->expected_annual_return > 0 
                ? ($actualROI / $project->expected_annual_return) * 100 
                : 0,
            'funding_efficiency' => $project->getFundingProgress(),
            'contributor_satisfaction' => $project->community_approval_rating,
            'project_duration_actual' => $project->actual_completion_date 
                ? $project->created_at->diffInMonths($project->actual_completion_date)
                : null,
            'project_duration_planned' => $project->project_duration_months,
            'on_time_performance' => $this->calculateOnTimePerformance($project),
        ];
    }

    public function findProjectsByFundingProgress(float $minProgress, float $maxProgress): array
    {
        $projects = CommunityProject::whereRaw('
            (current_amount / target_amount * 100) BETWEEN ? AND ?
        ', [$minProgress, $maxProgress])
        ->with(['contributions', 'creator'])
        ->orderBy('funding_end_date')
        ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function getProjectTimeline(ProjectId $projectId): array
    {
        $project = CommunityProject::with(['updates'])->find($projectId->value());

        if (!$project) {
            return [];
        }

        return [
            'project_id' => $project->id,
            'timeline' => $project->getProjectTimeline(),
            'milestones' => $project->project_milestones ?? [],
            'updates' => $project->updates->map(function ($update) {
                return [
                    'id' => $update->id,
                    'title' => $update->title,
                    'content' => $update->content,
                    'update_type' => $update->update_type,
                    'created_at' => $update->created_at,
                ];
            })->toArray(),
        ];
    }

    public function findProjectsWithUpcomingDeadlines(int $days = 30): array
    {
        $projects = CommunityProject::where(function ($query) use ($days) {
            $query->where('status', 'funding')
                  ->where('funding_end_date', '<=', now()->addDays($days))
                  ->where('funding_end_date', '>=', now());
        })
        ->orWhere(function ($query) use ($days) {
            $query->where('status', 'active')
                  ->where('expected_completion_date', '<=', now()->addDays($days))
                  ->where('expected_completion_date', '>=', now());
        })
        ->with(['contributions', 'creator'])
        ->orderBy('funding_end_date')
        ->orderBy('expected_completion_date')
        ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function getProjectCategoryStats(): array
    {
        $stats = CommunityProject::selectRaw('
            type,
            COUNT(*) as project_count,
            SUM(target_amount) as total_target,
            SUM(current_amount) as total_raised,
            AVG(expected_annual_return) as avg_return,
            AVG(community_approval_rating) as avg_rating
        ')
        ->groupBy('type')
        ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->type => [
                'project_count' => $stat->project_count,
                'total_target' => (float) $stat->total_target,
                'total_raised' => (float) $stat->total_raised,
                'funding_rate' => $stat->total_target > 0 
                    ? ($stat->total_raised / $stat->total_target) * 100 
                    : 0,
                'avg_return' => (float) $stat->avg_return,
                'avg_rating' => (float) $stat->avg_rating,
            ]];
        })->toArray();
    }

    public function findTopPerformingProjects(int $limit = 10): array
    {
        $projects = CommunityProject::with(['contributions', 'profitDistributions'])
            ->where('status', 'active')
            ->orderBy('community_approval_rating', 'desc')
            ->orderBy('expected_annual_return', 'desc')
            ->limit($limit)
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    public function updateProjectFunding(ProjectId $projectId, ContributionAmount $amount): void
    {
        CommunityProject::where('id', $projectId->value())
            ->increment('current_amount', $amount->value());
    }

    public function updateProjectStatus(ProjectId $projectId, ProjectStatus $status): void
    {
        CommunityProject::where('id', $projectId->value())
            ->update(['status' => $status->value()]);
    }

    public function searchProjects(string $query, array $filters = []): array
    {
        $projectQuery = CommunityProject::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('detailed_description', 'LIKE', "%{$query}%");
            });

        // Apply filters
        if (!empty($filters['status'])) {
            $projectQuery->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $projectQuery->where('type', $filters['type']);
        }

        if (!empty($filters['risk_level'])) {
            $projectQuery->where('risk_level', $filters['risk_level']);
        }

        if (!empty($filters['tier'])) {
            $projectQuery->forTier($filters['tier']);
        }

        if (!empty($filters['min_return'])) {
            $projectQuery->where('expected_annual_return', '>=', $filters['min_return']);
        }

        if (!empty($filters['max_return'])) {
            $projectQuery->where('expected_annual_return', '<=', $filters['max_return']);
        }

        $projects = $projectQuery->with(['contributions', 'creator'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('community_approval_rating', 'desc')
            ->get();

        return $projects->map(fn($project) => $this->toProjectArray($project))->toArray();
    }

    private function toProjectArray(CommunityProject $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'description' => $project->description,
            'detailed_description' => $project->detailed_description,
            'type' => $project->type,
            'category' => $project->category,
            'target_amount' => $project->target_amount,
            'current_amount' => $project->current_amount,
            'minimum_contribution' => $project->minimum_contribution,
            'maximum_contribution' => $project->maximum_contribution,
            'expected_annual_return' => $project->expected_annual_return,
            'project_duration_months' => $project->project_duration_months,
            'funding_start_date' => $project->funding_start_date,
            'funding_end_date' => $project->funding_end_date,
            'project_start_date' => $project->project_start_date,
            'expected_completion_date' => $project->expected_completion_date,
            'actual_completion_date' => $project->actual_completion_date,
            'status' => $project->status,
            'risk_level' => $project->risk_level,
            'requires_voting' => $project->requires_voting,
            'is_featured' => $project->is_featured,
            'auto_approve_contributions' => $project->auto_approve_contributions,
            'required_membership_tiers' => $project->required_membership_tiers,
            'tier_contribution_limits' => $project->tier_contribution_limits,
            'tier_voting_weights' => $project->tier_voting_weights,
            'project_manager_id' => $project->project_manager_id,
            'created_by' => $project->created_by,
            'project_milestones' => $project->project_milestones,
            'risk_factors' => $project->risk_factors,
            'success_metrics' => $project->success_metrics,
            'featured_image_url' => $project->featured_image_url,
            'gallery_images' => $project->gallery_images,
            'documents' => $project->documents,
            'total_contributors' => $project->total_contributors,
            'total_votes' => $project->total_votes,
            'community_approval_rating' => $project->community_approval_rating,
            'funding_progress' => $project->getFundingProgress(),
            'remaining_amount' => $project->getRemainingFundingAmount(),
            'days_remaining' => $project->getDaysRemainingForFunding(),
            'is_funding_active' => $project->isFundingActive(),
            'statistics' => $project->getProjectStatistics(),
            'timeline' => $project->getProjectTimeline(),
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at,
        ];
    }

    private function calculateFundingVelocity(CommunityProject $project): float
    {
        $daysSinceFundingStart = max(1, now()->diffInDays($project->funding_start_date));
        return $project->current_amount / $daysSinceFundingStart;
    }

    private function calculateProjectedCompletionDate(CommunityProject $project): ?string
    {
        if ($project->current_amount >= $project->target_amount) {
            return null; // Already funded
        }

        $velocity = $this->calculateFundingVelocity($project);
        if ($velocity <= 0) {
            return null;
        }

        $remainingAmount = $project->target_amount - $project->current_amount;
        $daysToCompletion = $remainingAmount / $velocity;

        return now()->addDays($daysToCompletion)->toDateString();
    }

    private function getAttentionReason(CommunityProject $project): string
    {
        if ($project->status === 'funding' && $project->funding_end_date <= now()->addDays(7)) {
            return 'Funding deadline approaching';
        }

        if ($project->status === 'active' && $project->expected_completion_date <= now()->addDays(30)) {
            return 'Project completion deadline approaching';
        }

        return 'Requires attention';
    }

    private function calculateOnTimePerformance(CommunityProject $project): ?float
    {
        if (!$project->actual_completion_date || !$project->expected_completion_date) {
            return null;
        }

        $plannedDuration = $project->created_at->diffInDays($project->expected_completion_date);
        $actualDuration = $project->created_at->diffInDays($project->actual_completion_date);

        if ($plannedDuration <= 0) {
            return null;
        }

        return ($plannedDuration / $actualDuration) * 100;
    }
}