<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Community\Repositories\ProfitDistributionRepository;
use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Domain\MLM\ValueObjects\UserId;
use App\Models\ProjectProfitDistribution;
use App\Models\ProjectContribution;
use DateTimeImmutable;

class EloquentProfitDistributionRepository implements ProfitDistributionRepository
{
    public function findById(int $id): ?array
    {
        $distribution = ProjectProfitDistribution::with(['user', 'project', 'contribution'])->find($id);
        return $distribution ? $this->toDistributionArray($distribution) : null;
    }

    public function findByProjectId(ProjectId $projectId): array
    {
        $distributions = ProjectProfitDistribution::forProject($projectId->value())
            ->with(['user', 'project', 'contribution'])
            ->orderBy('calculated_at', 'desc')
            ->get();

        return $distributions->map(fn($d) => $this->toDistributionArray($d))->toArray();
    }

    public function findByUserId(UserId $userId): array
    {
        $distributions = ProjectProfitDistribution::forUser($userId->value())
            ->with(['user', 'project', 'contribution'])
            ->orderBy('calculated_at', 'desc')
            ->get();

        return $distributions->map(fn($d) => $this->toDistributionArray($d))->toArray();
    }

    public function findByStatus(string $status): array
    {
        $distributions = ProjectProfitDistribution::byStatus($status)
            ->with(['user', 'project', 'contribution'])
            ->orderBy('calculated_at', 'desc')
            ->get();

        return $distributions->map(fn($d) => $this->toDistributionArray($d))->toArray();
    }

    public function findByType(string $distributionType): array
    {
        $distributions = ProjectProfitDistribution::byType($distributionType)
            ->with(['user', 'project', 'contribution'])
            ->orderBy('calculated_at', 'desc')
            ->get();

        return $distributions->map(fn($d) => $this->toDistributionArray($d))->toArray();
    }

    public function findPendingDistributions(): array
    {
        return $this->findByStatus('calculated');
    }

    public function findApprovedDistributions(): array
    {
        return $this->findByStatus('approved');
    }

    public function findByPeriod(string $periodLabel): array
    {
        $distributions = ProjectProfitDistribution::forPeriod($periodLabel)
            ->with(['user', 'project', 'contribution'])
            ->orderBy('calculated_at', 'desc')
            ->get();

        return $distributions->map(fn($d) => $this->toDistributionArray($d))->toArray();
    }

    public function calculateDistribution(
        int $contributionId,
        ContributionAmount $totalProfit,
        string $distributionType,
        string $periodLabel,
        DateTimeImmutable $periodStart,
        DateTimeImmutable $periodEnd
    ): array {
        $contribution = ProjectContribution::find($contributionId);
        if (!$contribution) {
            return [];
        }

        $distribution = ProjectProfitDistribution::calculateDistribution(
            $contribution,
            $totalProfit->value(),
            $distributionType,
            $periodLabel,
            \Carbon\Carbon::instance($periodStart),
            \Carbon\Carbon::instance($periodEnd)
        );

        return $this->toDistributionArray($distribution);
    }

    public function calculateProjectDistributions(
        ProjectId $projectId,
        ContributionAmount $totalProfit,
        string $distributionType,
        string $periodLabel,
        DateTimeImmutable $periodStart,
        DateTimeImmutable $periodEnd
    ): array {
        $project = \App\Models\CommunityProject::find($projectId->value());
        if (!$project) {
            return [];
        }

        $distributions = ProjectProfitDistribution::calculateProjectDistributions(
            $project,
            $totalProfit->value(),
            $distributionType,
            $periodLabel,
            \Carbon\Carbon::instance($periodStart),
            \Carbon\Carbon::instance($periodEnd)
        );

        return array_map(fn($d) => $this->toDistributionArray($d), $distributions);
    }

    public function getUserDistributionSummary(UserId $userId): array
    {
        return ProjectProfitDistribution::getUserDistributionSummary(
            \App\Models\User::find($userId->value())
        );
    }

    public function getProjectDistributionSummary(ProjectId $projectId): array
    {
        $distributions = ProjectProfitDistribution::forProject($projectId->value())->get();
        
        return [
            'project_id' => $projectId->value(),
            'total_distributions' => $distributions->count(),
            'total_amount_distributed' => $distributions->where('status', 'paid')->sum('distribution_amount'),
            'pending_amount' => $distributions->whereIn('status', ['calculated', 'approved'])->sum('distribution_amount'),
            'by_status' => $distributions->groupBy('status')->map->count(),
            'by_type' => $distributions->groupBy('distribution_type')->map->count(),
        ];
    }

    public function findByDateRange(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $distributions = ProjectProfitDistribution::whereBetween('calculated_at', [
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ])
        ->with(['user', 'project', 'contribution'])
        ->orderBy('calculated_at', 'desc')
        ->get();

        return $distributions->map(fn($d) => $this->toDistributionArray($d))->toArray();
    }

    public function getDistributionStatsByTier(): array
    {
        $stats = ProjectProfitDistribution::selectRaw('
            tier_at_distribution,
            COUNT(*) as distribution_count,
            SUM(distribution_amount) as total_amount,
            AVG(distribution_amount) as avg_amount
        ')
        ->groupBy('tier_at_distribution')
        ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->tier_at_distribution => [
                'distribution_count' => $stat->distribution_count,
                'total_amount' => (float) $stat->total_amount,
                'avg_amount' => (float) $stat->avg_amount,
            ]];
        })->toArray();
    }

    public function getDistributionPerformanceMetrics(): array
    {
        $totalDistributions = ProjectProfitDistribution::count();
        $totalAmount = ProjectProfitDistribution::sum('distribution_amount');
        $paidAmount = ProjectProfitDistribution::paid()->sum('distribution_amount');

        return [
            'total_distributions' => $totalDistributions,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'payment_rate' => $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0,
            'avg_distribution' => $totalDistributions > 0 ? $totalAmount / $totalDistributions : 0,
        ];
    }

    public function findTopEarningUsers(int $limit = 10): array
    {
        $users = ProjectProfitDistribution::selectRaw('
            user_id,
            COUNT(*) as distribution_count,
            SUM(distribution_amount) as total_earned
        ')
        ->paid()
        ->with('user')
        ->groupBy('user_id')
        ->orderBy('total_earned', 'desc')
        ->limit($limit)
        ->get();

        return $users->map(function ($user) {
            return [
                'user_id' => $user->user_id,
                'user' => $user->user,
                'distribution_count' => $user->distribution_count,
                'total_earned' => (float) $user->total_earned,
            ];
        })->toArray();
    }

    public function getTierBonusMultipliers(): array
    {
        return [
            'Bronze' => 1.0,
            'Silver' => 1.05,
            'Gold' => 1.10,
            'Diamond' => 1.15,
            'Elite' => 1.20,
        ];
    }

    public function findDistributionsRequiringAttention(): array
    {
        $distributions = ProjectProfitDistribution::where('status', 'approved')
            ->where('approved_at', '<=', now()->subDays(7))
            ->with(['user', 'project', 'contribution'])
            ->orderBy('approved_at')
            ->get();

        return $distributions->map(fn($d) => $this->toDistributionArray($d))->toArray();
    }

    public function save(array $distributionData): int
    {
        $distribution = ProjectProfitDistribution::create($distributionData);
        return $distribution->id;
    }

    public function updateStatus(int $distributionId, string $status): void
    {
        ProjectProfitDistribution::where('id', $distributionId)->update(['status' => $status]);
    }

    public function markAsPaid(int $distributionId, array $paymentData): void
    {
        $distribution = ProjectProfitDistribution::find($distributionId);
        if ($distribution) {
            $distribution->markAsPaid(
                \App\Models\User::find($paymentData['paid_by']),
                $paymentData['payment_reference'] ?? null,
                $paymentData['payment_method'] ?? null
            );
        }
    }

    public function cancel(int $distributionId, string $reason): void
    {
        $distribution = ProjectProfitDistribution::find($distributionId);
        if ($distribution) {
            $distribution->cancel($reason);
        }
    }

    public function delete(int $distributionId): void
    {
        ProjectProfitDistribution::where('id', $distributionId)->delete();
    }

    private function toDistributionArray(ProjectProfitDistribution $distribution): array
    {
        return [
            'id' => $distribution->id,
            'community_project_id' => $distribution->community_project_id,
            'user_id' => $distribution->user_id,
            'project_contribution_id' => $distribution->project_contribution_id,
            'distribution_amount' => $distribution->distribution_amount,
            'contribution_amount' => $distribution->contribution_amount,
            'contribution_percentage' => $distribution->contribution_percentage,
            'project_profit_amount' => $distribution->project_profit_amount,
            'distribution_rate' => $distribution->distribution_rate,
            'distribution_type' => $distribution->distribution_type,
            'period_start' => $distribution->period_start,
            'period_end' => $distribution->period_end,
            'distribution_period_label' => $distribution->distribution_period_label,
            'status' => $distribution->status,
            'calculated_at' => $distribution->calculated_at,
            'approved_at' => $distribution->approved_at,
            'paid_at' => $distribution->paid_at,
            'cancelled_at' => $distribution->cancelled_at,
            'approved_by' => $distribution->approved_by,
            'paid_by' => $distribution->paid_by,
            'payment_reference' => $distribution->payment_reference,
            'payment_method' => $distribution->payment_method,
            'notes' => $distribution->notes,
            'tier_at_distribution' => $distribution->tier_at_distribution,
            'tier_bonus_multiplier' => $distribution->tier_bonus_multiplier,
            'user' => $distribution->user,
            'project' => $distribution->project,
            'contribution' => $distribution->contribution,
        ];
    }
}