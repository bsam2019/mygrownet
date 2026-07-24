<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\Referral;
use App\Domain\GrowNet\Repositories\ReferralRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\NetworkLevel;
use App\Infrastructure\Persistence\Eloquent\GrowNet\UserNetwork;
use DateTimeImmutable;

class EloquentReferralRepository implements ReferralRepositoryInterface
{
    public function findByReferrerId(MemberId $referrerId): array
    {
        return UserNetwork::where('referrer_id', $referrerId->value())
            ->with(['user' => fn($q) => $q->select('id', 'name', 'email', 'phone', 'created_at')])
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByReferrerIdAndLevel(MemberId $referrerId, NetworkLevel $level): array
    {
        return UserNetwork::where('referrer_id', $referrerId->value())
            ->where('level', $level->value())
            ->with(['user' => fn($q) => $q->select('id', 'name', 'email', 'phone', 'created_at')])
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByReferredMemberId(MemberId $referredMemberId): array
    {
        return UserNetwork::where('user_id', $referredMemberId->value())
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function countByReferrerId(MemberId $referrerId): int
    {
        return UserNetwork::where('referrer_id', $referrerId->value())->count();
    }

    public function countByReferrerIdAndLevel(MemberId $referrerId, NetworkLevel $level): int
    {
        return UserNetwork::where('referrer_id', $referrerId->value())
            ->where('level', $level->value())
            ->count();
    }

    public function countActiveByReferrerId(MemberId $referrerId): int
    {
        return UserNetwork::where('referrer_id', $referrerId->value())
            ->whereHas('user.subscriptions', fn($q) => $q->where('status', 'active'))
            ->count();
    }

    public function countByReferrerIdAndMonth(MemberId $referrerId, int $month, int $year): int
    {
        return UserNetwork::where('referrer_id', $referrerId->value())
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();
    }

    public function getMaxLevelByReferrerId(MemberId $referrerId): int
    {
        return (int) UserNetwork::where('referrer_id', $referrerId->value())->max('level') ?? 0;
    }

    public function getTotalNetworkSize(MemberId $referrerId): int
    {
        return UserNetwork::where('referrer_id', $referrerId->value())->count();
    }

    public function getActiveNetworkMembers(MemberId $referrerId): int
    {
        return UserNetwork::where('referrer_id', $referrerId->value())
            ->whereHas('user.subscriptions', fn($q) => $q->where('status', 'active'))
            ->count();
    }

    public function getLevelBreakdown(MemberId $referrerId): array
    {
        $breakdown = [];
        for ($level = 1; $level <= 7; $level++) {
            $total = UserNetwork::where('referrer_id', $referrerId->value())
                ->where('level', $level)->count();
            $active = UserNetwork::where('referrer_id', $referrerId->value())
                ->where('level', $level)
                ->whereHas('user.subscriptions', fn($q) => $q->where('status', 'active'))
                ->count();
            $breakdown[] = [
                'level' => $level,
                'total_members' => $total,
                'active_members' => $active,
                'percentage_active' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
            ];
        }
        return $breakdown;
    }

    public function getNetworkGrowthData(MemberId $referrerId, int $months = 6): array
    {
        $growth = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = UserNetwork::where('referrer_id', $referrerId->value())
                ->where('created_at', '<=', $month->endOfMonth())
                ->count();
            $growth[] = ['month' => $month->format('Y-m'), 'count' => $count];
        }
        return $growth;
    }

    public function getDetailedNetwork(MemberId $referrerId, int $maxDepth = 3): array
    {
        return $this->buildTree($referrerId, 1, $maxDepth);
    }

    private function buildTree(MemberId $referrerId, int $currentLevel, int $maxDepth): array
    {
        if ($currentLevel > $maxDepth) return [];

        $members = UserNetwork::where('referrer_id', $referrerId->value())
            ->where('level', $currentLevel)
            ->with(['user' => fn($q) => $q->select('id', 'name', 'email', 'phone', 'created_at')])
            ->get();

        return $members->map(function ($m) use ($currentLevel, $maxDepth) {
            $user = $m->user;
            $node = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'level' => $currentLevel,
                'joined_date' => $user->created_at->format('M Y'),
                'children' => [],
            ];
            if ($currentLevel < $maxDepth) {
                $node['children'] = $this->buildTree(
                    new MemberId($user->id),
                    $currentLevel + 1,
                    $maxDepth
                );
            }
            return $node;
        })->toArray();
    }

    private function toDomain($model): Referral
    {
        $user = $model->user;
        return new Referral(
            id: $model->id,
            referrerId: new MemberId($model->referrer_id),
            referredMemberId: new MemberId($model->user_id),
            referredName: $user?->name ?? 'Unknown',
            referredEmail: $user?->email ?? '',
            level: new NetworkLevel($model->level),
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            tier: $user?->currentMembershipTier?->name ?? null,
            isActive: $user?->hasActiveSubscription() ?? false,
            personalVolume: (float) ($model->personal_volume ?? 0),
        );
    }
}
