<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\TeamVolume;
use App\Domain\GrowNet\Repositories\TeamVolumeRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Infrastructure\Persistence\Eloquent\GrowNet\TeamVolume as TeamVolumeModel;
use DateTimeImmutable;

class EloquentTeamVolumeRepository implements TeamVolumeRepositoryInterface
{
    public function findCurrentByMemberId(MemberId $memberId): ?TeamVolume
    {
        $model = TeamVolumeModel::where('user_id', $memberId->value())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findLatestByMemberId(MemberId $memberId): ?TeamVolume
    {
        $model = TeamVolumeModel::where('user_id', $memberId->value())->latest()->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findHistoryByMemberId(MemberId $memberId, int $months = 6): array
    {
        $models = TeamVolumeModel::where('user_id', $memberId->value())
            ->where('created_at', '>=', now()->subMonths($months))
            ->orderBy('created_at')
            ->get();
        return $models->map(fn($m) => $this->toDomain($m))->toArray();
    }

    public function sumByMemberIdAndMonth(MemberId $memberId, int $month, int $year): float
    {
        return (float) TeamVolumeModel::where('user_id', $memberId->value())
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('team_volume');
    }

    public function sumTeamVolumeByMemberId(MemberId $memberId): float
    {
        return (float) TeamVolumeModel::where('user_id', $memberId->value())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('team_volume');
    }

    public function save(TeamVolume $teamVolume): TeamVolume
    {
        $data = $teamVolume->toArray();
        unset($data['id']);
        if ($teamVolume->id() > 0) {
            TeamVolumeModel::where('id', $teamVolume->id())->update($data);
            return $teamVolume;
        }
        $model = TeamVolumeModel::create($data);
        return $this->toDomain($model);
    }

    public function sumBySource(MemberId $memberId, string $source, int $month, int $year): float
    {
        // Volume by source uses different tables depending on source type
        return 0;
    }

    private function toDomain($model): TeamVolume
    {
        return new TeamVolume(
            id: $model->id,
            memberId: new MemberId($model->user_id),
            personalVolume: (float) ($model->personal_volume ?? 0),
            teamVolume: (float) ($model->team_volume ?? 0),
            leftLegVolume: (float) ($model->left_leg_volume ?? 0),
            rightLegVolume: (float) ($model->right_leg_volume ?? 0),
            totalVolume: (float) ($model->total_volume ?? 0),
            activeReferralsCount: (int) ($model->active_referrals_count ?? 0),
            periodStart: new DateTimeImmutable(($model->period_start ?? $model->created_at)->format('Y-m-d')),
            periodEnd: new DateTimeImmutable(($model->period_end ?? $model->created_at)->format('Y-m-d')),
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
        );
    }
}
