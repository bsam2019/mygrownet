<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\LoyaltyPoints;
use App\Domain\GrowNet\Repositories\LoyaltyPointsRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Infrastructure\Persistence\Eloquent\GrowNet\PointTransaction;
use DateTimeImmutable;

class EloquentLoyaltyPointsRepository implements LoyaltyPointsRepositoryInterface
{
    public function findByMemberId(MemberId $memberId): array
    {
        return PointTransaction::where('user_id', $memberId->value())
            ->latest()
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function sumByMemberId(MemberId $memberId, string $type = 'lp'): float
    {
        $column = $type === 'bp' ? 'bp_amount' : 'lp_amount';
        return (float) PointTransaction::where('user_id', $memberId->value())->sum($column);
    }

    public function sumByMemberIdAndMonth(MemberId $memberId, int $month, int $year, string $type = 'lp'): float
    {
        $column = $type === 'bp' ? 'bp_amount' : 'lp_amount';
        return (float) PointTransaction::where('user_id', $memberId->value())
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum($column);
    }

    public function save(LoyaltyPoints $points): LoyaltyPoints
    {
        $data = [
            'user_id' => $points->memberId()->value(),
            'lp_amount' => $points->lpAmount(),
            'bp_amount' => $points->bpAmount(),
            'type' => $points->type(),
            'description' => $points->type(),
            'status' => $points->status(),
        ];

        if ($points->id() > 0) {
            PointTransaction::where('id', $points->id())->update($data);
            return $points;
        }

        $model = PointTransaction::create($data);
        return $this->toDomain($model);
    }

    private function toDomain($model): LoyaltyPoints
    {
        return new LoyaltyPoints(
            id: $model->id,
            memberId: new MemberId($model->user_id),
            lpAmount: (float) ($model->lp_amount ?? 0),
            bpAmount: (float) ($model->bp_amount ?? 0),
            type: $model->type ?? 'earned',
            description: $model->description ?? '',
            status: $model->status ?? 'completed',
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
        );
    }
}
