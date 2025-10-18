<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\MLM\Repositories\TeamVolumeRepository;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use App\Models\ReferralCommission;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentTeamVolumeRepository implements TeamVolumeRepository
{
    public function getCurrentTeamVolume(UserId $userId): ?TeamVolumeAmount
    {
        $teamVolume = TeamVolume::where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->first();

        return $teamVolume ? TeamVolumeAmount::fromFloat($teamVolume->team_volume) : null;
    }

    public function getTeamVolumeForPeriod(
        UserId $userId, 
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): ?TeamVolumeAmount {
        $teamVolume = TeamVolume::where('user_id', $userId->value())
            ->where('period_start', '>=', $startDate->format('Y-m-d'))
            ->where('period_end', '<=', $endDate->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();

        return $teamVolume ? TeamVolumeAmount::fromFloat($teamVolume->team_volume) : null;
    }

    public function calculateTeamVolumeRollup(UserId $userId): TeamVolumeAmount
    {
        // Get all network members up to 5 levels deep
        $networkMembers = UserNetwork::where('referrer_id', $userId->value())
            ->where('level', '<=', 5)
            ->pluck('user_id')
            ->toArray();

        if (empty($networkMembers)) {
            return TeamVolumeAmount::fromFloat(0);
        }

        // Calculate total volume from network commissions and purchases
        $totalVolume = ReferralCommission::whereIn('referred_id', $networkMembers)
            ->where('status', 'paid')
            ->sum('package_amount') ?? 0;

        // Add personal volume from direct purchases
        $personalVolume = ReferralCommission::where('referred_id', $userId->value())
            ->where('status', 'paid')
            ->sum('package_amount') ?? 0;

        return TeamVolumeAmount::fromFloat($totalVolume + $personalVolume);
    }

    public function getNetworkVolumeAggregation(UserId $userId, int $maxDepth = 5): array
    {
        $query = DB::table('user_networks as un')
            ->join('referral_commissions as rc', 'un.user_id', '=', 'rc.referred_id')
            ->where('un.referrer_id', $userId->value())
            ->where('un.level', '<=', $maxDepth)
            ->where('rc.status', 'paid')
            ->selectRaw('
                un.level,
                COUNT(DISTINCT un.user_id) as member_count,
                SUM(rc.package_amount) as level_volume,
                AVG(rc.package_amount) as avg_package_size
            ')
            ->groupBy('un.level')
            ->orderBy('un.level')
            ->get();

        return $query->map(function ($row) {
            return [
                'level' => $row->level,
                'member_count' => $row->member_count,
                'level_volume' => (float) $row->level_volume,
                'avg_package_size' => (float) $row->avg_package_size,
            ];
        })->toArray();
    }

    public function updateTeamVolume(
        UserId $userId, 
        TeamVolumeAmount $personalVolume, 
        TeamVolumeAmount $teamVolume,
        int $activeReferralsCount,
        DateTimeImmutable $periodStart,
        DateTimeImmutable $periodEnd
    ): void {
        TeamVolume::updateOrCreate(
            [
                'user_id' => $userId->value(),
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
            ],
            [
                'personal_volume' => $personalVolume->value(),
                'team_volume' => $teamVolume->value(),
                'active_referrals_count' => $activeReferralsCount,
                'team_depth' => $this->calculateTeamDepth($userId),
            ]
        );
    }

    public function getUsersEligibleForPerformanceBonuses(TeamVolumeAmount $minimumVolume): array
    {
        $users = TeamVolume::where('team_volume', '>=', $minimumVolume->value())
            ->with('user')
            ->orderBy('team_volume', 'desc')
            ->get();

        return $users->map(function ($teamVolume) {
            return [
                'user_id' => $teamVolume->user_id,
                'team_volume' => $teamVolume->team_volume,
                'performance_bonus' => $teamVolume->calculatePerformanceBonus(),
                'user' => $teamVolume->user,
            ];
        })->toArray();
    }

    public function getTeamVolumeStats(
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): array {
        $stats = TeamVolume::whereBetween('period_start', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])
        ->selectRaw('
            COUNT(*) as total_users,
            SUM(personal_volume) as total_personal_volume,
            SUM(team_volume) as total_team_volume,
            AVG(team_volume) as avg_team_volume,
            MAX(team_volume) as max_team_volume,
            SUM(active_referrals_count) as total_active_referrals
        ')
        ->first();

        return [
            'total_users' => $stats->total_users ?? 0,
            'total_personal_volume' => (float) ($stats->total_personal_volume ?? 0),
            'total_team_volume' => (float) ($stats->total_team_volume ?? 0),
            'avg_team_volume' => (float) ($stats->avg_team_volume ?? 0),
            'max_team_volume' => (float) ($stats->max_team_volume ?? 0),
            'total_active_referrals' => $stats->total_active_referrals ?? 0,
        ];
    }

    public function getTeamVolumeHistory(UserId $userId, int $months = 12): array
    {
        $history = TeamVolume::where('user_id', $userId->value())
            ->where('created_at', '>=', now()->subMonths($months))
            ->orderBy('period_start', 'desc')
            ->get();

        return $history->map(function ($volume) {
            return [
                'period_start' => $volume->period_start,
                'period_end' => $volume->period_end,
                'personal_volume' => (float) $volume->personal_volume,
                'team_volume' => (float) $volume->team_volume,
                'active_referrals_count' => $volume->active_referrals_count,
                'team_depth' => $volume->team_depth,
                'performance_bonus' => $volume->calculatePerformanceBonus(),
            ];
        })->toArray();
    }

    public function calculateTeamDepth(UserId $userId): int
    {
        return UserNetwork::where('referrer_id', $userId->value())
            ->max('level') ?? 0;
    }

    public function getActiveReferralsCount(UserId $userId): int
    {
        // Count active referrals (users with recent activity or subscriptions)
        return UserNetwork::where('referrer_id', $userId->value())
            ->whereHas('user', function ($query) {
                $query->where('last_activity_at', '>=', now()->subDays(30))
                    ->orWhereHas('subscriptions', function ($subQuery) {
                        $subQuery->where('status', 'active');
                    });
            })
            ->count();
    }

    public function bulkUpdateTeamVolumes(array $volumeUpdates): void
    {
        DB::transaction(function () use ($volumeUpdates) {
            foreach ($volumeUpdates as $update) {
                $this->updateTeamVolume(
                    UserId::fromInt($update['user_id']),
                    TeamVolumeAmount::fromFloat($update['personal_volume']),
                    TeamVolumeAmount::fromFloat($update['team_volume']),
                    $update['active_referrals_count'],
                    new DateTimeImmutable($update['period_start']),
                    new DateTimeImmutable($update['period_end'])
                );
            }
        });
    }

    public function getTopPerformersByTeamVolume(int $limit = 10): array
    {
        $topPerformers = TeamVolume::with('user')
            ->orderBy('team_volume', 'desc')
            ->limit($limit)
            ->get();

        return $topPerformers->map(function ($volume, $index) {
            return [
                'rank' => $index + 1,
                'user_id' => $volume->user_id,
                'user' => $volume->user,
                'team_volume' => (float) $volume->team_volume,
                'active_referrals_count' => $volume->active_referrals_count,
                'team_depth' => $volume->team_depth,
                'performance_bonus' => $volume->calculatePerformanceBonus(),
            ];
        })->toArray();
    }

    public function checkTierUpgradeQualification(
        UserId $userId, 
        TeamVolumeAmount $requiredVolume, 
        int $requiredReferrals
    ): bool {
        $teamVolume = TeamVolume::where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$teamVolume) {
            return false;
        }

        return $teamVolume->team_volume >= $requiredVolume->value() &&
               $teamVolume->active_referrals_count >= $requiredReferrals;
    }
}