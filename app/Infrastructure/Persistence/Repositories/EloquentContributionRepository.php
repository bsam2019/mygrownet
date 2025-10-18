<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Community\Repositories\ContributionRepository;
use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Domain\MLM\ValueObjects\UserId;
use App\Models\ProjectContribution;
use App\Models\CommunityProject;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentContributionRepository implements ContributionRepository
{
    public function findById(int $id): ?array
    {
        $contribution = ProjectContribution::with(['user', 'project'])->find($id);
        return $contribution ? $this->toContributionArray($contribution) : null;
    }

    public function findByUserId(UserId $userId): array
    {
        $contributions = ProjectContribution::forUser($userId->value())
            ->with(['project', 'user'])
            ->orderBy('contributed_at', 'desc')
            ->get();

        return $contributions->map(fn($c) => $this->toContributionArray($c))->toArray();
    }

    public function findByProjectId(ProjectId $projectId): array
    {
        $contributions = ProjectContribution::where('community_project_id', $projectId->value())
            ->with(['user', 'project'])
            ->orderBy('contributed_at', 'desc')
            ->get();

        return $contributions->map(fn($c) => $this->toContributionArray($c))->toArray();
    }

    public function findByStatus(string $status): array
    {
        $contributions = ProjectContribution::where('status', $status)
            ->with(['user', 'project'])
            ->orderBy('contributed_at', 'desc')
            ->get();

        return $contributions->map(fn($c) => $this->toContributionArray($c))->toArray();
    }

    public function findPendingContributions(): array
    {
        return $this->findByStatus('pending');
    }

    public function getUserProjectContribution(UserId $userId, ProjectId $projectId): ContributionAmount
    {
        $total = ProjectContribution::where('user_id', $userId->value())
            ->where('community_project_id', $projectId->value())
            ->confirmed()
            ->sum('amount');

        return ContributionAmount::fromFloat($total);
    }

    public function getUserTotalContributions(UserId $userId): ContributionAmount
    {
        $total = ProjectContribution::where('user_id', $userId->value())
            ->confirmed()
            ->sum('amount');

        return ContributionAmount::fromFloat($total);
    }

    public function findByDateRange(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $contributions = ProjectContribution::whereBetween('contributed_at', [
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ])
        ->with(['user', 'project'])
        ->orderBy('contributed_at', 'desc')
        ->get();

        return $contributions->map(fn($c) => $this->toContributionArray($c))->toArray();
    }

    public function getContributionStatsByTier(): array
    {
        $stats = ProjectContribution::selectRaw('
            tier_at_contribution,
            COUNT(*) as contribution_count,
            SUM(amount) as total_amount,
            AVG(amount) as avg_amount
        ')
        ->confirmed()
        ->groupBy('tier_at_contribution')
        ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->tier_at_contribution => [
                'contribution_count' => $stat->contribution_count,
                'total_amount' => (float) $stat->total_amount,
                'avg_amount' => (float) $stat->avg_amount,
            ]];
        })->toArray();
    }

    public function getContributionStatsByProject(): array
    {
        $stats = ProjectContribution::join('community_projects', 'project_contributions.community_project_id', '=', 'community_projects.id')
            ->selectRaw('
                community_projects.name as project_name,
                community_projects.id as project_id,
                COUNT(*) as contribution_count,
                SUM(project_contributions.amount) as total_amount,
                AVG(project_contributions.amount) as avg_amount
            ')
            ->where('project_contributions.status', 'confirmed')
            ->groupBy('community_projects.id', 'community_projects.name')
            ->get();

        return $stats->map(function ($stat) {
            return [
                'project_id' => $stat->project_id,
                'project_name' => $stat->project_name,
                'contribution_count' => $stat->contribution_count,
                'total_amount' => (float) $stat->total_amount,
                'avg_amount' => (float) $stat->avg_amount,
            ];
        })->toArray();
    }

    public function findTopContributors(int $limit = 10): array
    {
        $contributors = ProjectContribution::selectRaw('
            user_id,
            COUNT(*) as contribution_count,
            SUM(amount) as total_amount
        ')
        ->confirmed()
        ->with('user')
        ->groupBy('user_id')
        ->orderBy('total_amount', 'desc')
        ->limit($limit)
        ->get();

        return $contributors->map(function ($contributor) {
            return [
                'user_id' => $contributor->user_id,
                'user' => $contributor->user,
                'contribution_count' => $contributor->contribution_count,
                'total_amount' => (float) $contributor->total_amount,
            ];
        })->toArray();
    }

    public function findContributionsEligibleForReturns(ProjectId $projectId): array
    {
        $contributions = ProjectContribution::where('community_project_id', $projectId->value())
            ->confirmed()
            ->whereHas('project', function ($query) {
                $query->where('status', 'active');
            })
            ->with(['user', 'project'])
            ->get();

        return $contributions->map(fn($c) => $this->toContributionArray($c))->toArray();
    }

    public function getUserContributionHistory(UserId $userId): array
    {
        $contributions = ProjectContribution::where('user_id', $userId->value())
            ->with(['project'])
            ->orderBy('contributed_at', 'desc')
            ->get();

        return $contributions->map(function ($contribution) {
            $contributionArray = $this->toContributionArray($contribution);
            $contributionArray['expected_returns'] = $contribution->calculateExpectedReturns();
            $contributionArray['roi_percentage'] = $contribution->getReturnOnInvestment();
            return $contributionArray;
        })->toArray();
    }

    public function calculateUserVotingPower(UserId $userId, ProjectId $projectId): float
    {
        $contribution = ProjectContribution::where('user_id', $userId->value())
            ->where('community_project_id', $projectId->value())
            ->confirmed()
            ->first();

        return $contribution ? $contribution->getVotingPower() : 0.0;
    }

    public function findContributionsByTier(string $tierName): array
    {
        $contributions = ProjectContribution::where('tier_at_contribution', $tierName)
            ->confirmed()
            ->with(['user', 'project'])
            ->orderBy('contributed_at', 'desc')
            ->get();

        return $contributions->map(fn($c) => $this->toContributionArray($c))->toArray();
    }

    public function getContributionPerformanceMetrics(): array
    {
        $totalContributions = ProjectContribution::confirmed()->count();
        $totalAmount = ProjectContribution::confirmed()->sum('amount');
        $avgContribution = $totalContributions > 0 ? $totalAmount / $totalContributions : 0;

        $monthlyStats = ProjectContribution::selectRaw('
            DATE_FORMAT(contributed_at, "%Y-%m") as month,
            COUNT(*) as count,
            SUM(amount) as amount
        ')
        ->confirmed()
        ->where('contributed_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return [
            'total_contributions' => $totalContributions,
            'total_amount' => $totalAmount,
            'avg_contribution' => $avgContribution,
            'monthly_trends' => $monthlyStats->toArray(),
        ];
    }

    public function findContributionsRequiringAttention(): array
    {
        $contributions = ProjectContribution::where('status', 'pending')
            ->where('contributed_at', '<=', now()->subDays(7))
            ->with(['user', 'project'])
            ->orderBy('contributed_at')
            ->get();

        return $contributions->map(fn($c) => $this->toContributionArray($c))->toArray();
    }

    public function save(array $contributionData): int
    {
        $contribution = ProjectContribution::create($contributionData);
        return $contribution->id;
    }

    public function updateStatus(int $contributionId, string $status): void
    {
        ProjectContribution::where('id', $contributionId)->update(['status' => $status]);
    }

    public function delete(int $contributionId): void
    {
        ProjectContribution::where('id', $contributionId)->delete();
    }

    private function toContributionArray(ProjectContribution $contribution): array
    {
        return [
            'id' => $contribution->id,
            'user_id' => $contribution->user_id,
            'community_project_id' => $contribution->community_project_id,
            'amount' => $contribution->amount,
            'status' => $contribution->status,
            'tier_at_contribution' => $contribution->tier_at_contribution,
            'contributed_at' => $contribution->contributed_at,
            'confirmed_at' => $contribution->confirmed_at,
            'cancelled_at' => $contribution->cancelled_at,
            'transaction_reference' => $contribution->transaction_reference,
            'payment_method' => $contribution->payment_method,
            'total_returns_received' => $contribution->total_returns_received,
            'expected_annual_return' => $contribution->expected_annual_return,
            'auto_reinvest' => $contribution->auto_reinvest,
            'notes' => $contribution->notes,
            'contribution_percentage' => $contribution->getContributionPercentage(),
            'voting_power' => $contribution->getVotingPower(),
            'is_eligible_for_returns' => $contribution->isEligibleForReturns(),
            'user' => $contribution->user,
            'project' => $contribution->project,
        ];
    }
}